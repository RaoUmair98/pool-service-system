{{-- Status switch and buttons shared by every master form. Expects $model and $cancel; $saveAndNew adds a second save button,
     $activeHint replaces the text under the switch and $lockActive shows the switch without letting it change. --}}
<div class="form-actions-bar">
    <div class="custom-control custom-switch mb-0">
        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
            @checked(old('is_active', $model->is_active)) @disabled($lockActive ?? false)>
        <label class="custom-control-label" for="is_active">Active</label>
        <small class="text-muted ml-2 d-none d-md-inline">{{ $activeHint ?? 'Turn off to hide from new entries. History stays safe.' }}</small>
    </div>
    <div class="form-actions-buttons">
        <button type="submit" class="btn btn-primary waves-effect waves-light"><i class="mdi mdi-content-save"></i> Save</button>
        @if (($saveAndNew ?? false) && ! $model->exists)
            <button type="submit" name="action" value="save_and_new" class="btn btn-success waves-effect waves-light">
                <i class="mdi mdi-plus"></i> Save &amp; Add Another
            </button>
        @endif
        <a href="{{ $cancel }}" class="btn btn-light waves-effect">Cancel</a>
    </div>
</div>
