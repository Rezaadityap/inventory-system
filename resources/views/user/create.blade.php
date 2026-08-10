<div x-show="createModal" x-cloak x-transition-opacity @keydown.escape.window="createModal = false" class="modal-overlay">
    <div x-show="createModal" x-transition @click.outside="createModal = false" class="modal-panel">
        <form action="{{ route('user.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h2>Tambah User</h2>
                <span class="modal-close" @click="createModal = false">&times;</span>
            </div>

            <div class="modal-body space-y-4">
                <div>
                    <label for="name" class="form-label required">Nama</label>
                    <input type="text" name="name" id="name" class="form-input" value="{{ old('name') }}"
                        placeholder="Masukan Nama Lengkap" required>
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div>
                    <label for="email" class="form-label required">Email</label>
                    <input type="email" name="email" id="email" class="form-input" value="{{ old('email') }}"
                        placeholder="Masukan Email" required>
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>
                <div>
                    <label for="username" class="form-label required">Username</label>
                    <input type="text" name="username" id="username" class="form-input"
                        value="{{ old('username') }}" placeholder="Masukan Username" required>
                    <x-input-error :messages="$errors->get('username')" class="mt-1" />
                </div>
                <div>
                    <label for="password" class="form-label required">Password</label>
                    <input type="password" name="password" id="password" class="form-input"
                        placeholder="Masukan Password" required>
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>
                <div>
                    <label for="password_confirmation" class="form-label required">Ulangi Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="form-input" placeholder="Ulangi Password" required>
                </div>
                <div>
                    <label for="photo" class="form-label">Foto</label>
                    <input type="file" name="photo" id="photo" accept="image/*"
                        class="form-input cursor-pointer">
                    <x-input-error :messages="$errors->get('photo')" class="mt-1" />
                </div>
                <div>
                    <label for="role_id" class="form-label required">Hak Akses</label>
                    <select name="role_id" id="role_id" class="form-input cursor-pointer" required>
                        <option value="" disabled @selected(!old('role_id'))>Pilih Hak Akses</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('role_id')" class="mt-1" />
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn" @click="createModal = false">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
