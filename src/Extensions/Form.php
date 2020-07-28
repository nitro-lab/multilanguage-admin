<?php

namespace NitroLab\MultilanguageAdmin\Extensions;

use NitroLab\MultilanguageAdmin\Extensions\NestedForm;
use Encore\Admin\Form as AdminForm;
use Encore\Admin\Form\Field;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Validation\Validator;
use Symfony\Component\HttpFoundation\Response;

class Form extends AdminForm
{


    /**
     *  Update relation data.
     *
     * @param array $relationsData
     * @throws Exception
     */
    protected function updateRelation($relationsData)
    {
        foreach ($relationsData as $name => $values) {
            if (!method_exists($this->model, $name)) {
                continue;
            }

            $relation = $this->model->$name();

            $oneToOneRelation = $relation instanceof Relations\HasOne
                || $relation instanceof Relations\MorphOne
                || $relation instanceof Relations\BelongsTo;

            $prepared = $this->prepareUpdate([$name => $values], $oneToOneRelation);

            if (empty($prepared)) {
                continue;
            }

            switch (true) {
                case $relation instanceof Relations\BelongsToMany:
                case $relation instanceof Relations\MorphToMany:
                    if (isset($prepared[$name])) {
                        $relation->sync($prepared[$name]);
                    }
                    break;
                case $relation instanceof Relations\HasOne:

                    $related = $this->model->$name;

                    // if related is empty
                    if (is_null($related)) {
                        $related = $relation->getRelated();
                        $qualifiedParentKeyName = $relation->getQualifiedParentKeyName();
                        $localKey = Arr::last(explode('.', $qualifiedParentKeyName));
                        $related->{$relation->getForeignKeyName()} = $this->model->{$localKey};
                    }
                    foreach ($prepared[$name] as $column => $value) {
                        if (!method_exists($related, $column)) {
                            $related->setAttribute($column, $value);
                            continue;
                        }
                        $relation = call_user_func([$related, $column]);

                        if ($relation instanceof Relations\Relation) {
                            if(!empty($value)){
                                $this->updateChildRelation($related, $column, $value);
                                if(isset($related[$column]))unset($related[$column]);
                            }
                        }
                    }

                    $related->save();
                    break;
                case $relation instanceof Relations\BelongsTo:
                case $relation instanceof Relations\MorphTo:

                    $parent = $this->model->$name;

                    // if related is empty
                    if (is_null($parent)) {
                        $parent = $relation->getRelated();
                    }

                    foreach ($prepared[$name] as $column => $value) {
                        $parent->setAttribute($column, $value);
                    }

                    $parent->save();

                    // When in creating, associate two models
                    $foreignKeyMethod = version_compare(app()->version(), '5.8.0', '<') ? 'getForeignKey' : 'getForeignKeyName';
                    if (!$this->model->{$relation->{$foreignKeyMethod}()}) {
                        $this->model->{$relation->{$foreignKeyMethod}()} = $parent->getKey();

                        $this->model->save();
                    }

                    break;
                case $relation instanceof Relations\MorphOne:
                    $related = $this->model->{$name};

                    if ($related === null) {
                        $related = $relation->make();
                    }

                    /** @var Relations\Relation $relation */
                    $relation = $this->model()->$name();
                    $keyName = $relation->getRelated()->getKeyName();
                    $instance = $relation->findOrNew(Arr::get($related, $keyName));


                    foreach ($prepared[$name] as $column => $value) {
                        if (!method_exists($instance, $column)) {
                            $instance->setAttribute($column, $value);
                            continue;
                        }
                        $relation = call_user_func([$instance, $column]);
                        if ($relation instanceof Relations\Relation) {
                            if(!empty($value)){
                                $this->updateChildRelation($instance, $column, $value);
                            }
                        }
                    }

                    $instance->save();
                    break;
                case $relation instanceof Relations\HasMany:
                case $relation instanceof Relations\MorphMany:

                    foreach ($prepared[$name] as $related) {
                        /** @var Relations\Relation $relation */
                        $relation = $this->model()->$name();

                        $keyName = $relation->getRelated()->getKeyName();
                        $instance = $relation->findOrNew(Arr::get($related, $keyName));

                        if ($related[static::REMOVE_FLAG_NAME] == 1) {
                            $instance->delete();
                            continue;
                        }

                        Arr::forget($related, static::REMOVE_FLAG_NAME);

                        foreach ($related as $col => $value){
                            if (!method_exists($instance, $col)) {
                                continue;
                            }

                            $relation = call_user_func([$instance, $col]);

                            if ($relation instanceof Relations\Relation) {
                                if(!empty($value)){
                                    $this->updateChildRelation($instance, $col, $value);
                                    if(isset($related[$col]))unset($related[$col]);
                                }
                            }
                        }

                        $instance->fill($related);
                        $instance->save();
                    }

                    break;
            }
        }
    }

    /**
     * @param $model
     * @param $relationName
     * @param $relationsData
     * @throws Exception
     */
    protected function updateChildRelation($model, $relationName, $relationsData)
    {
        $model->save();
        /** @var Relations\Relation $relation */
        $relation = $model->{$relationName}();

        if ($relation instanceof Relations\MorphOne) {
            /** @var Relations\Relation $relation */
            $related = $model->$relationName;

            if ($related === null) {
                $related = $relation->make();
            }

            foreach ($relationsData as $column => $value) {
                $related->setAttribute($column, $value);
            }

            $related->save();
        } elseif ($relation instanceof Relations\HasMany) {
            foreach ($relationsData as $related) {
                $relation = $model->{$relationName}();
                $keyName = $relation->getRelated()->getKeyName();
                $instance = $relation->findOrNew(Arr::get($related, $keyName));
                if ($related[static::REMOVE_FLAG_NAME] == 1) {
                    $instance->delete();
                    continue;
                }
                Arr::forget($related, static::REMOVE_FLAG_NAME);
                $instance->fill($related);
                $instance->save();
            }
        }
    }

    /**
     * Store a new record.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\Http\JsonResponse
     */
    public function store()
    {
        $data = \request()->all();

        // Handle validation errors.
        if ($validationMessages = $this->validationMessages($data)) {
            return $this->responseValidationError($validationMessages);
        }

        if (($response = $this->prepare($data)) instanceof Response) {
            return $response;
        }
        DB::transaction(function () {
            $inserts = $this->prepareInsert($this->updates);

            if(isset($this->updates[$this->model->getKeyName()])){
                $inserts[$this->model->getKeyName()] = $this->updates[$this->model->getKeyName()];
            }

            foreach ($inserts as $column => $value) {
                $this->model->setAttribute($column, $value);
            }
            $this->model->save();

            $this->updateRelation($this->relations);
        });

        if (($response = $this->callSaved()) instanceof Response) {
            return $response;
        }

        if ($response = $this->ajaxResponse(trans('admin.save_succeeded'))) {
            return $response;
        }

        return $this->redirectAfterStore();
    }

    /**
     * Get validation messages.
     *
     * @param array $input
     *
     * @return MessageBag|bool
     */
    public function validationMessages($input)
    {
        $failedValidators = [];

        /** @var Field $field */
        foreach ($this->fields() as $field) {
            if($field instanceof LangTabAll){
                $validator = $field->getValidator($input);
            }else if (!$validator = $field->getValidator($input)) {
                continue;
            }

            if (($validator instanceof Validator) && !$validator->passes()) {
                $failedValidators[] = $validator;
            }
        }

        $message = $this->mergeValidationMessages($failedValidators);

        return $message->any() ? $message : false;
    }
}
