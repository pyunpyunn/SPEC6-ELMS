<div><label for="code">Department Code</label><input id="code" name="code" autocomplete="organization" value="{{ old('code', $department?->code) }}" required></div>
<div><label for="name">Name</label><input id="name" name="name" autocomplete="organization" value="{{ old('name', $department?->name) }}" required></div>
<div><label for="manager_user_id">Manager</label><select id="manager_user_id" name="manager_user_id" autocomplete="off"><option value="">Unassigned</option>@foreach($managers as $manager)<option value="{{ $manager->id }}" @selected(old('manager_user_id', $department?->manager_user_id) == $manager->id)>{{ $manager->name }}</option>@endforeach</select></div>
<div><label for="is_active">Status</label><select id="is_active" name="is_active" autocomplete="off"><option value="1" @selected(old('is_active', $department?->is_active ?? 1) == 1)>Active</option><option value="0" @selected(old('is_active', $department?->is_active) === 0)>Inactive</option></select></div>
<div class="full"><label for="description">Description</label><textarea id="description" name="description" autocomplete="off">{{ old('description', $department?->description) }}</textarea></div>
<div class="full"><button class="btn primary">{{ $button }}</button></div>

