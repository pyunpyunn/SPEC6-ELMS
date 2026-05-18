<div><label>Department Code</label><input name="code" value="{{ old('code', $department?->code) }}" required></div>
<div><label>Name</label><input name="name" value="{{ old('name', $department?->name) }}" required></div>
<div><label>Manager</label><select name="manager_user_id"><option value="">Unassigned</option>@foreach($managers as $manager)<option value="{{ $manager->id }}" @selected(old('manager_user_id', $department?->manager_user_id) == $manager->id)>{{ $manager->name }}</option>@endforeach</select></div>
<div><label>Status</label><select name="is_active"><option value="1" @selected(old('is_active', $department?->is_active ?? 1) == 1)>Active</option><option value="0" @selected(old('is_active', $department?->is_active) === 0)>Inactive</option></select></div>
<div class="full"><label>Description</label><textarea name="description">{{ old('description', $department?->description) }}</textarea></div>
<div class="full"><button class="btn primary">{{ $button }}</button></div>
