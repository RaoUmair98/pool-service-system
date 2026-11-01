{{-- Status switch and buttons shared by every master form. Expects $model and $cancel; $saveAndNew adds a second save button,
     $activeHint replaces the text under the switch and $lockActive shows the switch without letting it change. --}}
<div class="form-group">
    <div class="custom-control custom-switch">
        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
            @checked(old('is_active', $model->is_active)) @disabled($lockActive ?? false)>
        <label class="custom-control-label" for="is_active">Active</label>
    </div>
    <small class="form-text text-muted">{{ $activeHint ?? 'Turn this off to hide it from new entries. Old records and history stay safe.' }}</small>
</div>

<button type="submit" class="btn btn-primary waves-effect waves-light"><i class="mdi mdi-content-save"></i> Save</button>
@if (($saveAndNew ?? false) && ! $model->exists)
    <button type="submit" name="action" value="save_and_new" class="btn btn-success waves-effect waves-light ml-1">
        <i class="mdi mdi-plus"></i> Save &amp; Add Another
    </button>
@endif
<a href="{{ $cancel }}" class="btn btn-light waves-effect ml-1">Cancel</a>
