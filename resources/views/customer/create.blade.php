<div x-show="createModal" x-cloak x-transition-opacity @keydown.escape.window="createModal = false" class="modal-overlay">
    <div x-show="createModal" x-transition @click.outside="createModal = false" class="modal-panel">
        <form action="{{ route('customers.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h2>Tambah Customer</h2>
                <span class="modal-close" @click="createModal = false">&times;</span>
            </div>

            <div class="modal-body space-y-4">
                <div>
                    <label for="customer_name" class="form-label required">Nama Customer</label>
                    <input type="text" name="customer_name" id="customer_name" class="form-input"
                        value="{{ old('customer_name') }}" placeholder="Masukan Nama Customer" required>
                    <x-input-error :messages="$errors->get('customer_name')" class="mt-1" />
                </div>
                <div>
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-input" value="{{ old('email') }}"
                        placeholder="Masukan Email">
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>
                <div>
                    <label for="phone_number" class="form-label required">No. Hp</label>
                    <input type="number" name="phone_number" id="phone_number" class="form-input"
                        value="{{ old('phone_number') }}" placeholder="Masukan Username" required>
                    <x-input-error :messages="$errors->get('phone_number')" class="mt-1" />
                </div>
                <div>
                    <label for="company_photo" class="form-label">Foto Perusahaan</label>
                    <input type="file" name="company_photo" id="company_photo" accept="image/*"
                        class="form-input cursor-pointer">
                    <x-input-error :messages="$errors->get('company_photo')" class="mt-1" />
                </div>
                <div>
                    <label for="address" class="form-label">Alamat Perusahaan</label>
                    <textarea name="address" id="address" cols="8" rows="3" class="form-input"
                        placeholder="Masukkan Alamat Perusahaan">{{ old('address') }}</textarea>
                    <x-input-error :messages="$errors->get('address')" class="mt-1" />
                </div>
                <div>
                    <label for="user_id" class="form-label">Nama PIC</label>
                    <select name="user_id" id="user_id" class="form-input">
                        <option value="">Pilih PIC</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ (string) old('user_id') === (string) $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('user_id')" class="mt-1" />
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn" @click="createModal = false">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
