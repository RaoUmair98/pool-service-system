@foreach ($customers as $customer)
    <tr>
        <td class="name-cell">
            <a href="{{ route('customers.show', $customer) }}">{{ $customer->name }}</a>
        </td>
        <td>{{ $customer->phone ?: '-' }}</td>
        <td>{{ $customer->email ?: '-' }}</td>
        <td class="text-wrap" style="max-width: 280px;">{{ $customer->service_address }}</td>
        <td><span class="badge badge-{{ $customer->is_active ? 'success' : 'secondary' }}">{{ $customer->is_active ? 'Active' : 'Turned off' }}</span></td>
        <td>
            @include('partials.row-actions', ['name' => 'customers', 'model' => $customer])
        </td>
    </tr>
@endforeach
