<div x-show="editModal" x-cloak x-transition.opacity @keydown.escape.window="editModal = false" class="modal-overlay">
    <div x-show="editModal" x-transition @click.outside="editModal = false" class="modal-panel">
        <form :action="`/user/${editData.id}`" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="modal-header">
                <h2>Edit User</h2>
                <span class="modal-close" @click="editModal = false">&times;</span>
            </div>

            <div class="modal-body space-y-4">
                <div>
                    <label class="form-label required">Nama</label>
                    <input type="text" name="name" class="form-input" x-model="editData.name" required>
                </div>

                <div>
                    <label class="form-label required">Username</label>
                    <input type="text" name="username" class="form-input" x-model="editData.username" required>
                </div>

                <div>
                    <label class="form-label required">Email</label>
                    <input type="email" name="email" class="form-input" x-model="editData.email" required>
                </div>

                <div>
                    <label class="form-label required">Hak Akses</label>
                    <select name="role_id" class="form-input cursor-pointer" x-model="editData.role_id" required>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Foto (kosongkan jika tidak diganti)</label>
                    <input type="file" name="photo" class="form-input" accept="image/*">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn" @click="editModal = false">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
