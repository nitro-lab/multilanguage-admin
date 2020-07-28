# z-song Laravel-admin multilanguage support

## Allow to multilanguage support z-song\laravel-admin based by laravel-translatable

How to use:

in app/Admin/bootstrap.php

    use Encore\Admin\Form;
    use NitroLab\MultilanguageAdmin\Extensions\LangTabAll;
    
    Form::extend('langtaball', LangTabAll::class);
    
    
model need to be:

    use Astrotomic\Translatable\Translatable;
    use Illuminate\Database\Eloquent\Model;
    
    class MyModel extends Model
    {
        use Translatable;
    
        protected $fillable = ['alias'];
    
        public $translatedAttributes = ['title', 'body'];
    }

and then in admin controller form:
    
    use NitroLab\MultilanguageAdmin\Form;
    use NitroLab\MultilanguageAdmin\Form\NestedForm;

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MyModel());

        $form->tab('General', function(Form $form){
            $form->text('alias', __('Alias'));
            $form->switch('released', __('Released'));

            $form->langtaball('translations', function (NestedForm $form) {
                $form->text('title')->rules('required|string|min:6|max:255');
                $form->textarea('body');
            });
        });

        return $form;
    }
