<?php

namespace NitroLab\MultilanguageAdmin\Form;

use NitroLab\MultilanguageAdmin\Form;
use Encore\Admin\Form\Field\HasMany as EncoreHasMany;
use Encore\Admin\Form\NestedForm;

/**
 * Class HasMany.
 */
class HasMany extends EncoreHasMany
{
    /**
     * Build a Nested form.
     *
     * @param string   $column
     * @param \Closure $builder
     * @param null     $model
     *
     * @return NestedForm
     */
    protected function buildNestedForm($column, \Closure $builder, $model = null)
    {
        $form = new Form\NestedForm($column, $model);

        $form->setForm($this->form);

        call_user_func($builder, $form);

        $form->hidden($this->getKeyName());

        $form->hidden(NestedForm::REMOVE_FLAG_NAME)->default(0)->addElementClass(NestedForm::REMOVE_FLAG_CLASS);

        return $form;
    }
}
