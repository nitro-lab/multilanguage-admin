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

        @foreach(config('translatable.locales') as $key => $locale)
            @if(is_array($locale))
                @foreach($locale as $national)
                    @php
                        $language_index = $key . '-' . $national;
                    @endphp
                    <li class="@if ($locale == config('app.locale')) active @endif">
                        <a href="#{{ str_replace('.', '-', $relationName) . '_' . $language_index }}" data-toggle="tab">
                            {{ config('translatable.native_locale.' . $language_index, $language_index) }} <i
                                class="fa fa-exclamation-circle text-red hide"></i>
                        </a>
                    </li>
                @endforeach
            @else
                <li class="@if ($locale == config('app.locale')) active @endif">
                    <a href="#{{ str_replace('.', '-', $relationName) . '_' . $locale }}" data-toggle="tab">
                        {{ config('translatable.native_locale.' . $locale, $locale) }} <i
                            class="fa fa-exclamation-circle text-red hide"></i>
                    </a>
                </li>
            @endif

        @endforeach

    </ul>

    <div class="tab-content has-many-{{$column}}-forms">
        @if(!empty($forms))
            @foreach(config('translatable.locales') as $key => $locale)
                @if(isset($forms[$locale]))
                    @php
                        $form = $forms[$locale];
                    @endphp
                    <div
                        class="tab-pane fields-group has-many-{{$column}}-form @if ($locale == config('app.locale')) active @endif"
                        id="{{ str_replace('.', '-', $relationName) . '_' . $locale }}">
                        @foreach($form->fields() as $field)
                            {!! $field->render() !!}
                        @endforeach
                    </div>
                @else
                    @include(
                        'multilanguage-admin::langTabAllNewLocale',
                        compact('column', 'template_fields', 'locale')
                    )
                @endif
            @endforeach
        @else
            @foreach(config('translatable.locales') as $key => $locale)

                @if(is_array($locale))
                    @foreach($locale as $national)
                        @php
                            $language_index = $key . '-' . $national;
                        @endphp
                        @include(
                            'multilanguage-admin::langTabAllNewLocale',
                            array_merge(['locale' => $language_index], compact('column', 'template_fields'))
                        )
                    @endforeach

                @else
                    @include(
                        'multilanguage-admin::langTabAllNewLocale',
                        compact('column', 'template_fields', 'locale')
                    )
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
