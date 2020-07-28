<style>
    .nav-tabs > li:hover > i {
        display: inline;
    }

    .close-tab {
        position: absolute;
        font-size: 10px;
        top: 2px;
        right: 5px;
        color: #94A6B0;
        cursor: pointer;
        display: none;
    }
</style>
@php if(stripos($column, '.')){
    $column = str_replace('.', '[', $column) . ']';
} @endphp
<div id="has-many-{{$column}}" class="nav-tabs-custom has-many-{{$column}}">
    <div class="row header">
        <div class="col-md-2 {{$viewClass['label']}}"><h4 class="pull-right">{{__('Translations')}}</h4></div>
    </div>

    <hr style="margin-top: 0px;">

    <ul class="nav nav-tabs">
        @if(!empty($forms))
            @foreach($forms as $pk => $form)
                <li class="@if ($form == reset($forms)) active @endif ">
                    <a href="#{{ str_replace('.', '-', $relationName) . '_' . $pk }}" data-toggle="tab">
                        {{ config('translatable.native_locale.' . $pk, $pk) }} <i class="fa fa-exclamation-circle text-red hide"></i>
                    </a>
                </li>
            @endforeach
        @else

            @foreach(config('translatable.locales') as $key => $locale)
                @if(is_array($locale))
                    @foreach($locale as $national)
                        @php
                            $language_index = $key . '-' . $national;
                        @endphp
                        <li class="@if ($locale == config('app.locale')) active @endif">
                            <a href="#{{ str_replace('.', '-', $relationName) . '_' . $language_index }}" data-toggle="tab">
                                {{ config('translatable.native_locale.' . $language_index, $language_index) }} <i class="fa fa-exclamation-circle text-red hide"></i>
                            </a>
                        </li>
                        @endforeach
                    @else
                    <li class="@if ($locale == config('app.locale')) active @endif">
                        <a href="#{{ str_replace('.', '-', $relationName) . '_' . $locale }}" data-toggle="tab">
                            {{ config('translatable.native_locale.' . $locale, $locale) }} <i class="fa fa-exclamation-circle text-red hide"></i>
                        </a>
                    </li>
                    @endif

            @endforeach
        @endif

    </ul>

    <div class="tab-content has-many-{{$column}}-forms">
        @if(!empty($forms))
            @foreach($forms as $pk => $form)
                <div class="tab-pane fields-group has-many-{{$column}}-form @if ($form == reset($forms)) active @endif"
                     id="{{ str_replace('.', '-', $relationName) . '_' . $pk }}">
                    @foreach($form->fields() as $field)
                        {!! $field->render() !!}
                    @endforeach
                </div>
            @endforeach
        @else
            @foreach(config('translatable.locales') as $key => $locale)
                @if(is_array($locale))
                    @foreach($locale as $national)
                        @php
                            $language_index = $key . '-' . $national;
                        @endphp
                        <div class="tab-pane fields-group has-many-{{$column}}-form @if ($language_index == config('app.locale')) active @endif" id="{{ str_replace('.', '-', $relationName) . '_' . $language_index }}">
                            @foreach($template_fields as $field)
                                @php
                                    $field->setElementName($column.'['. $language_index .'][' .$field->column() .']');
                                    $field->attribute('name',$column.'['. $language_index .'][' .$field->column() .']');
                                    $field->attribute('title',$field->column());
                                    if($field->column() == 'locale'){
                                        $field->value($language_index);
                                    }
                                @endphp
                                {!! $field->render() !!}
                            @endforeach
                            <input type="hidden" name="{{$relationName}}[{{ $language_index }}][loc]" value ="{{ $language_index }}">
                        </div>
                    @endforeach

                    @else
                    <div class="tab-pane fields-group has-many-{{$column}}-form @if ($locale == config('app.locale')) active @endif" id="{{ str_replace('.', '-', $relationName) . '_' . $locale }}">
                        @foreach($template_fields as $field)
                            @php
                                $field->setElementName($column.'['. $locale .'][' .$field->column() .']');
                                $field->attribute('name',$column.'['. $locale .'][' .$field->column() .']');
                                $field->attribute('title',$field->column());
                                if($field->column() == 'locale'){
                                    $field->value($locale);
                                }
                            @endphp
                            {!! $field->render() !!}
                        @endforeach
                        <input type="hidden" name="{{$relationName}}[{{ $locale }}][loc]" value ="{{ $locale }}">
                    </div>
                @endif

            @endforeach
        @endif
    </div>

    <template class="nav-tab-tpl">
        <li class="new">
            <a href="#{{ $relationName . '_new_' . \Encore\Admin\Form\NestedForm::DEFAULT_KEY_NAME }}"
               data-toggle="tab">
                &nbsp;New {{ \Encore\Admin\Form\NestedForm::DEFAULT_KEY_NAME }} <i
                    class="fa fa-exclamation-circle text-red hide"></i>
            </a>
            <i class="close-tab fa fa-times"></i>
        </li>
    </template>

</div>
