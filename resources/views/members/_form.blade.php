<div class="field">
    <label for="first_name">First Name</label>
    <input id="first_name" type="text" name="first_name" value="{{ old('first_name', $member->first_name ?? '') }}"
        required>
</div>

<div class="field">
    <label for="last_name">Last Name</label>
    <input id="last_name" type="text" name="last_name" value="{{ old('last_name', $member->last_name ?? '') }}"
        required>
</div>

<div class="field">
    <label for="email">Email</label>
    <input id="email" type="email" name="email" value="{{ old('email', $member->email ?? '') }}" required>
</div>

<div class="field">
    <label for="category_id">Category</label>
    <select id="category_id" name="category_id" required>
        @foreach ($categories as $category)
        <option value="{{ $category->id }}" @selected(old('category_id', $member->category_id ?? '') == $category->id)>
            {{ $category->name }}
        </option>
        @endforeach
    </select>
</div>

<div class="field">
    <label for="level_id">Level</label>
    <select id="level_id" name="level_id" required>
        @foreach ($levels as $level)
        <option value="{{ $level->id }}" @selected(old('level_id', $member->level_id ?? '') == $level->id)>
            {{ $level->name }}
        </option>
        @endforeach
    </select>
</div>

<div class="actions">
    <button class="btn" type="submit">
        {{ $submitLabel }}
    </button>

    <a class="btn btn-secondary" href="{{ route('members.index') }}">
        Cancel
    </a>
</div>