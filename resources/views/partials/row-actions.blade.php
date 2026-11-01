{{-- Edit / turn on-off / delete, with words so nobody has to guess an icon. Expects $name (route prefix) and $model. --}}
<div class="row-actions">
    <a href="{{ route("$name.edit", $model) }}" class="btn btn-xs btn-light"><i class="mdi mdi-pencil"></i> Edit</a>

    <form action="{{ route("$name.toggle", $model) }}" method="POST" class="d-inline">
        @csrf
        @method('PATCH')
        @if ($model->is_active)
            <button type="submit" class="btn btn-xs btn-light" title="Hide it from new entries. Nothing is deleted."><i class="mdi mdi-eye-off-outline"></i> Turn off</button>
        @else
            <button type="submit" class="btn btn-xs btn-success" title="Show it again in new entries."><i class="mdi mdi-eye-outline"></i> Turn on</button>
        @endif
    </form>

    <form action="{{ route("$name.destroy", $model) }}" method="POST" class="d-inline"
        data-confirm="Delete this for ever? If it is already in use it will not be deleted - use Turn off instead.">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-xs btn-soft-danger"><i class="mdi mdi-delete-outline"></i> Delete</button>
    </form>
</div>
