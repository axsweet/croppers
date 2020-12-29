<div class="{{$viewClass['form-group']}} {!! !$errors->has($errorKey) ? '' : 'has-error' !!}">
    <label for="{{$id}}" class="{{$viewClass['label']}} control-label">{{$label}}</label>
    <div class="{{$viewClass['field']}}">
        @include('admin::form.error')
        @for ($i = 1; $i <= 9; $i++)
        <div id="cropper-btn{{ $i }}" class="btn btn-info pull-left cropper-btn">{{ trans('admin_cropper.choose') }}</div>
        @endfor


        @include('admin::form.help-block')
        @for ($i = 1; $i <= 9; $i++)
        <input id="cropper-file{{$i}}" class="croppers-file" type="file" accept="image/*" {!! $attributes !!}/>
        @endfor

        <div style="clear: both"></div>
        @for ($i = 0; $i <= 8; $i++)
         @if (isset($preview[$i]) )
            <img id="cropper-img{{$i+1}}" class="croppers-img" {!! !isset($value[$i]) ? '' : 'src="'.$preview[$i].'"'  !!}>
        @else
            <img id="cropper-img{{$i+1}}" class="croppers-img" >
        @endif
        @endfor


        @for ($i = 1; $i <= 9; $i++)
        <input id="cropper-input{{$i}}" class="croppers-input" name="{{$name}}[]" value="{{ isset($value[$i-1]) ? old($column, $value[$i-1]) : '' }}"/>
        @endfor

    </div>
</div>