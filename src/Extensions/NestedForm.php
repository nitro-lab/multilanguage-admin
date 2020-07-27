<?php
/**
 * Created by PhpStorm.
 * User: akravchenko
 * Date: 07.05.2019
 * Time: 19:42
 */

namespace NitroLab\MultilanguageAdmin\Extensions;

use Encore\Admin\Form\Field;
use Encore\Admin\Form\NestedForm as EncoreNestedForm;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class NestedForm extends EncoreNestedForm
{
    /**
     * Do prepare work before store and update.
     *
     * @param array $record
     *
     * @return array
     */
    protected function prepareRecord($record)
    {
        try {
            if ($record[static::REMOVE_FLAG_NAME] == 1) {
                return $record;
            }
        } catch (\Exception $e){
//            dd($record);
        }

        $prepared = [];

        /* @var Field $field */
        foreach ($this->fields as $field) {
            $columns = $field->column();

            $value = $this->fetchColumnValue($record, $columns);

            if (is_null($value)) {
                continue;
            }

            if (method_exists($field, 'prepare')) {
                $value = $field->prepare($value);
            }

            if (($field instanceof \Encore\Admin\Form\Field\Hidden) || $value != $field->original()) {
                if (is_array($columns)) {
                    foreach ($columns as $name => $column) {
                        Arr::set($prepared, $column, $value[$name]);
                    }
                } elseif (is_string($columns)) {
                    Arr::set($prepared, $columns, $value);
                }
            }
        }

        $prepared[static::REMOVE_FLAG_NAME] = $record[static::REMOVE_FLAG_NAME];
        return $prepared;
    }

    /**
     * Prepare for insert or update.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function prepare($input)
    {
        foreach ($input as $key => $record) {
            $this->setFieldOriginalValue($key);
//            dump($record);
            $input[$key] = $this->prepareRecord($record);
        }

        return $input;
    }

    /**
     * Set `errorKey` `elementName` `elementClass` for fields inside hasmany fields.
     *
     * @param Field $field
     *
     * @return Field
     */
    protected function formatField(Field $field)
    {
        $column = $field->column();

        $elementName = $elementClass = $errorKey = [];

        $key = $this->getKey();

        if (is_array($column)) {
            foreach ($column as $k => $name) {
                $errorKey[$k] = sprintf('%s.%s.%s', $this->relationName, $key, $name);
                $elementName[$k] = sprintf('%s[%s][%s]', $this->relationName, $key, $name);
                $elementClass[$k] = [$this->relationName, $name];
            }
        } else {
            if(Str::contains($column, '.')){

                $columns = explode('.', $column);
                $errorKey = sprintf('%s.%s.%s.%s', $this->relationName, $key, $columns[0], $columns[1]);
                $elementName = sprintf('%s[%s][%s][%s]', $this->relationName, $key, $columns[0], $columns[1]);
                $elementClass = [$this->relationName, $columns[0]];
            } else {
                $key_prefix = str_replace(']', '', str_replace('[', '.', $this->relationName));
                $errorKey = sprintf('%s.%s.%s', $key_prefix, $key, $column);
                $elementName = sprintf('%s[%s][%s]', $this->relationName, $key, $column);
                $elementClass = [$this->relationName, $column];
            }
        }

        return $field->setErrorKey($errorKey)
            ->setElementName($elementName)
            ->setElementClass($elementClass);
    }
}
