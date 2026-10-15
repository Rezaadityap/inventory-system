<div x-show="createModal" x-cloak x-transition.opacity @keydown.escape.window="createModal = false" class="modal-overlay">
    <div x-show="createModal" x-transition @click.outside="createModal = false" class="modal-panel">
        <form action="{{ route('shipments.store') }}" method="POST">
            @csrf
            <div class="modal-header">
                <h2>Tambah Pengiriman</h2>
                <span class="modal-close" @click="createModal = false">&times;</span>
            </div>

            <div class="modal-body space-y-4">
                <div>
                    <label for="transaction_id" class="form-label required">Transaksi</label>
                    <select name="transaction_id" id="transaction_id" class="form-input" required>
                        <option value="">Pilih Transaksi</option>
                        @foreach ($transactions as $transaction)
                            <option value="{{ $transaction->id }}" @selected(old('transaction_id') == $transaction->id)>
                                {{ $transaction->transaction_number }} - {{ $transaction->customer->customer_name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('transaction_id')" class="mt-1" />
                </div>

                <div>
                    <label for="driver_id" class="form-label required">Pengemudi</label>
                    <select name="driver_id" id="driver_id" class="form-input" required @disabled($availableDrivers->isEmpty())>
                        <option value="">Pilih Pengemudi</option>
                        @foreach ($availableDrivers as $driver)
                            <option value="{{ $driver->id }}" @selected(old('driver_id') == $driver->id)>
                                {{ $driver->user->name }}
                            </option>
                        @endforeach
                    </select>
                    @if ($availableDrivers->isEmpty())
                        <p class="text-xs text-text-secondary mt-1">
                            Tidak ada pengemudi yang tersedia saat ini.
                        </p>
                    @endif
                    <x-input-error :messages="$errors->get('driver_id')" class="mt-1" />
                </div>

                <div>
                    <label for="vehicle_number" class="form-label required">Plat Nomor</label>
                    <input type="text" name="vehicle_number" id="vehicle_number" class="form-input" maxlength="20"
                        value="{{ old('vehicle_number') }}" placeholder="mis. B 1234 XYZ" required>
                    <x-input-error :messages="$errors->get('vehicle_number')" class="mt-1" />
                </div>

                <div>
                    <label for="planned_shipped_at" class="form-label required">Rencana Kirim</label>
                    <input type="date" name="planned_shipped_at" id="planned_shipped_at" class="form-input"
                        value="{{ old('planned_shipped_at') }}" min="{{ now()->format('Y-m-d') }}" required>
                    <x-input-error :messages="$errors->get('planned_shipped_at')" class="mt-1" />
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn" @click="createModal = false">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
