<div class="full"><label>Name</label><input name="name" value="{{ old('name', $type?->name) }}" placeholder="Bereavement Leave" required></div>
<div><label>Annual Allocation</label><input type="number" name="annual_allocation" value="{{ old('annual_allocation', $type?->annual_allocation ?? 15) }}" required></div>
<div><label>Status</label><select name="is_active"><option value="1" @selected(old('is_active', $type?->is_active ?? 1) == 1)>Active</option><option value="0" @selected(old('is_active', $type?->is_active) === 0)>Inactive</option></select></div>
<div><label>Requires Approval</label><select name="requires_approval"><option value="1" @selected(old('requires_approval', $type?->requires_approval ?? 1) == 1)>Yes</option><option value="0" @selected(old('requires_approval', $type?->requires_approval) === 0)>No</option></select></div>
<div><label>Requires Proof</label><select name="requires_proof"><option value="0" @selected(old('requires_proof', $type?->requires_proof ?? 0) == 0)>No / Conditional</option><option value="1" @selected(old('requires_proof', $type?->requires_proof) == 1)>Yes</option></select></div>
<div class="full"><label>Proof Rules</label><input name="proof_rules" value="{{ old('proof_rules', $type?->proof_rules) }}" placeholder="Sick leave needs medical certificate for 3+ days"></div>
<div class="full"><button class="btn primary">{{ $button }}</button></div>
