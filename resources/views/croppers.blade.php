
<div class="{{$viewClass['form-group']}} {!! !$errors->has($errorKey) ? '' : 'has-error' !!}">
    <label for="{{$id}}" class="{{$viewClass['label']}} control-label">{{$label}}</label>
    <div class="{{$viewClass['field']}}">
        @include('admin::form.error')
        <div id="cropper-btn1" class="btn btn-info pull-left cropper-btn">{{ trans('admin_cropper.choose') }}</div>
        <div id="cropper-btn2" class="btn btn-info pull-left cropper-btn">{{ trans('admin_cropper.choose') }}</div>
        <div id="cropper-btn3" class="btn btn-info pull-left cropper-btn">{{ trans('admin_cropper.choose') }}</div>
        @include('admin::form.help-block')
        <input id="cropper-file1" class="croppers-file" type="file" accept="image/*" {!! $attributes !!}/>
        <input id="cropper-file2" class="croppers-file" type="file" accept="image/*" {!! $attributes !!}/>
        <input id="cropper-file3" class="croppers-file" type="file" accept="image/*" {!! $attributes !!}/>

        <div style="clear: both"></div>

        @if (isset($preview[0]) && isset($preview[1]) && isset($preview[2]))
            <img id="cropper-img1" class="croppers-img" {!! !isset($value[0]) ? '' : 'src="'.$preview[0].'"'  !!}>
            <img id="cropper-img2" class="croppers-img" {!! !isset($value[1]) ? '' : 'src="'.$preview[1].'"'  !!}>
            <img id="cropper-img3" class="croppers-img" {!! !isset($value[2]) ? '' : 'src="'.$preview[2].'"'  !!}>
        @else
            <img id="cropper-img1" class="croppers-img" >
            <img id="cropper-img2" class="croppers-img" >
            <img id="cropper-img3" class="croppers-img" >
        @endif



        <input id="cropper-input1" class="croppers-input" name="{{$name}}[]" value="{{ isset($value[0]) ? old($column, $value[0]) : '' }}"/>
        <input id="cropper-input2" class="croppers-input" name="{{$name}}[]" value="{{ isset($value[1]) ? old($column, $value[1]) : '' }}"/>
        <input id="cropper-input3" class="croppers-input" name="{{$name}}[]" value="{{ isset($value[2]) ? old($column, $value[2]) : '' }}"/>
    </div>
</div>