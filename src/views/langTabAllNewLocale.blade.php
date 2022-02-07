

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
