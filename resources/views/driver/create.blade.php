<div x-show="createModal" x-cloak x-transition-opacity @keydown.escape.window="createModal = false" class="modal-overlay">
    <div x-show="createModal" x-transition @click.outside="createModal = false" class="modal-panel">
        <form action="{{ route('drivers.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h2>Tambah Pengemudi</h2>
                <span class="modal-close" @click="createModal = false">&times;</span>
            </div>

            <div class="modal-body space-y-4">
                <div>
                    <label for="name" class="form-label required">Nama Pengemudi</label>
                    <input type="text" name="name" id="name" class="form-input" value="{{ old('name') }}"
                        placeholder="Masukan Nama Pengemudi" required>
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div>
                    <label for="username" class="form-label required">Username</label>
                    <input type="text" name="username" id="username" class="form-input"
                        value="{{ old('username') }}" placeholder="Masukan Username" required>
                    <x-input-error :messages="$errors->get('username')" class="mt-1" />
                </div>

                <div>
                    <label for="email" class="form-label required">Email</label>
                    <input type="email" name="email" id="email" class="form-input" value="{{ old('email') }}"
                        placeholder="Masukan Email" required>
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <div>
                    <label for="password" class="form-label required">Password</label>
                    <input type="password" name="password" id="password" class="form-input"
                        value="{{ old('password') }}" placeholder="Masukan Password" required>
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <div>
                    <label for="password_confirmation" class="form-label required">Ulangi Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-input"
                        value="{{ old('password_confirmation') }}" placeholder="Ulangi Password" required>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                </div>

                <div>
                    <label for="photo" class="form-label">Foto</label>
                    <input type="file" name="photo" id="photo" accept="image/*"
                        class="form-input cursor-pointer">
                    <x-input-error :messages="$errors->get('photo')" class="mt-1" />
                </div>

                <div>
                    <label for="phone_number" class="form-label required">No. Hp</label>
                    <input type="text" inputmode="numeric" name="phone_number" id="phone_number" class="form-input"
                        maxlength="13" value="{{ old('phone_number') }}" placeholder="Masukan No. Hp" required>
                    <x-input-error :messages="$errors->get('phone_number')" class="mt-1" />
                </div>

                <div>
                    <label for="driver_license" class="form-label required">SIM</label>
                    <input type="text" inputmode="numeric" name="driver_license" id="driver_license"
                        class="form-input" maxlength="16" value="{{ old('driver_license') }}"
                        placeholder="Masukan Nomor SIM" required>
                    <x-input-error :messages="$errors->get('driver_license')" class="mt-1" />
                </div>

                <div>
                    <label for="available_status" class="form-label required">Status</label>
                    <select name="available_status" id="available_status" class="form-input" required>
                        @foreach (['Tersedia', 'Sibuk', 'Libur'] as $status)
                            <option value="{{ $status }}" @selected(old('available_status', 'Tersedia') === $status)>
                                {{ $status }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('available_status')" class="mt-1" />
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn" @click="createModal = false">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
