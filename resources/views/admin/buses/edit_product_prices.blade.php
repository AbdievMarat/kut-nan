@extends('layouts.admin')

@section('content')
    <div class="container-sm">
        <a href="{{ route('admin.buses.index') }}" class="btn btn-outline-primary mb-3">&lang; Назад</a>
        <div class="card">
            <div class="card-header">
                Редактирование цен на продукты для буса № {{ $bus->license_plate }}
            </div>
            <div class="card-body">
                <div class="row">
                    <form
                            action="{{ route('admin.buses.product_prices_update', ['bus' => $bus]) }}"
                            method="post"
                    >
                        @csrf
                        @method('put')

                        @foreach($products as $product)
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="product_{{ $product->id }}">{{ $product->name }}</span>
                                <input
                                        class="form-control"
                                        type="number"
                                        name="product_prices[{{ $product->id }}]"
                                        id="product_{{ $product->id }}"
                                        value="{{ $prices->has($product->id) ? $prices[$product->id]->price : old('product_prices.'.$product->id) }}"
                                        autocomplete="off"
                                >
                            </div>
                        @endforeach

                        <button type="submit" class="btn btn-success w-100 mb-3">Сохранить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection