{{-- File: resources/views/customers/tbody.blade.php --}}
@forelse ($customers as $customer)
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $customer->user->name ?? 'N/A' }}</td>
    <td>{{ $customer->user->email ?? 'N/A' }}</td>
    <td class="text-end">Rp {{ number_format($customer->saldo, 0, ',', '.') }}</td>
    <td>{{ $customer->created_at->format('d M Y H:i') }}</td>
    <td>
        <button class="btn btn-warning btn-sm btn-edit-customer" title="Edit Saldo {{ $customer->user->name ?? '' }}"
                data-customer="{{ json_encode($customer->load('user')) }}"> {{-- Load relasi user --}}
            <i class="fas fa-pencil-alt"></i>
        </button>
        <button class="btn btn-danger btn-sm btn-hapus-customer" title="Hapus Customer {{ $customer->user->name ?? '' }}"
                data-id="{{ $customer->id }}"
                data-customer="{{ json_encode($customer->load('user')) }}"> {{-- Load relasi user --}}
            <i class="fas fa-trash-alt"></i>
        </button>
    </td>
</tr>
@empty
<tr>
    <td colspan="6" class="text-center">Tidak ada data customer ditemukan.</td> {{-- Sesuaikan colspan --}}
</tr>
@endforelse