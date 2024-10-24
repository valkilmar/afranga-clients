<!-- home.blade.php -->

@extends('layout')

@section('title', 'Client create...')

@section('content')

    @php
        $name = old('name');
    @endphp

    <div class="container mt-3">
        <form method="post" action="{{ ($client && $client->id) ? route('client-update') : route('client-create') }}">
            @csrf
            <div class="row justify-content">
                <div class="col-8">
                    @if ($client)
                        <input type="hidden" name="client[id]" value="{{ $client->id }}" />
                    @endif

                    <h3>Personal Profile</h3>
                    <div class="form-floating mb-3">
                        <input name="client[name]" type="text" value="{{ old('client') ? old('client')['name'] : ($client ? $client->name : '') }}" class="form-control @error('client.name') is-invalid @enderror" required id="name" placeholder="Name">
                        <label for="floatingInput">Name <span class="text-danger">*</span></label>
                        @error('client.name')
                            <small class="invalid-feedback">{{ $errors->first('client.name') }}</small>
                        @enderror
                    </div>

                    <div class="form-floating mb-3">
                        <input name="client[personal_no]" type="text" value="{{ old('client') ? old('client')['personal_no'] : ($client ? $client->personal_no : '') }}" class="form-control @error('client.personal_no') is-invalid @enderror" required id="personal_no" placeholder="Personal No">
                        <label for="personal_no">Personal No <span class="text-danger">*</span></label>
                        @error('client.personal_no')
                            <small class="invalid-feedback">{{ $errors->first('client.personal_no') }}</small>
                        @enderror
                    </div>
                    

                    <div class="form-floating mb-3">
                        <input name="client[card_no]" type="text" value="{{ old('client') ? old('client')['card_no'] : ($client ? $client->card_no : '') }}" class="form-control @error('client.card_no') is-invalid @enderror" required id="card_no" placeholder="Document No">
                        <label for="card_no">Card No <span class="text-danger">*</span></label>
                        @error('client.card_no')
                            <small class="invalid-feedback">{{ $errors->first('client.card_no') }}</small>
                        @enderror
                    </div>
                    

                    <button type="submit" class="btn btn-primary btn-lg">Save</button>
                    <a class="btn btn-outline-secondary btn-lg" href="{{ route('client-index') }}">Cancel</a>
                </div>

                <div class="col-4">
                    <h3>Contact Info</h3>
                    @for ($i = 0; $i < 5; $i++)
                        <div class="form-floating mb-3">
                            <input name="client[phones][]" type="text" value="{{ old('client') ? old('client')['phones'][$i] : (isset($client->phones[$i]) ? $client->phones[$i]->number : '') }}" class="form-control @error("client.phones.{$i}") is-invalid @enderror" id="phone-{{ $i }}"
                                placeholder="Phone {{ $i + 1 }}">
                            <label for="phone-{{ $i + 1 }}">Phone {{ $i + 1 }}</label>
                            @error("client.phones.{$i}")
                                <small class="invalid-feedback">{{ $errors->first("client.phones.{$i}") }}</small>
                            @enderror
                        </div>
                    @endfor
                </div>
            </div>
        </form>
    </div>

@endsection
