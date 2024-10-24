<!-- home.blade.php -->

@extends('layout')

@section('title', 'Clients')

@section('content')


    <div class="container mt-3">
        <div class="row">

            @if (Session::has('message'))
                @php
                    [$messageClass, $messageBody] = explode('|', Session::get('message'));
                @endphp

                <div class="alert alert-{{ $messageClass }} alert-dismissible fade show" role="alert">
                    {{ $messageBody }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Toolbox --}}
            <div class="col-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('client-edit') }}">CREATE NEW</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link @disabled(!$prev)" href="{{ $prev }}">PREV</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link @disabled(!$next)" href="{{ $next }}">NEXT</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">EXPORT
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('client-export', [1000]) }}">1000 per file</a></li>
                            <li><a class="dropdown-item" href="{{ route('client-export', [100]) }}">100 per file</a></li>
                            <li><a class="dropdown-item" href="{{ route('client-export', [10]) }}">10 per file</a></li>
                        </ul>
                    </li>
                </ul>

                @if ($exportStatus)
                    <h5>Exports available</h5>
                    <small>
                        @if ($exportStatus['timeDone'] < 0)
                            Processing...
                        @else
                            Expire in {{ $exportStatus['timeLeft'] }} sec.
                        @endif
                    </small>
                    <table class="table table-condensed table-striped">

                        <body>
                            @foreach ($exportStatus['files'] as $fileName => $url)
                                <tr>
                                    <td><a href="{{ $url }}">{{ $fileName }}</a></td>
                                </tr>
                            @endforeach
                        </body>
                    </table>
                @endif
            </div>


            {{-- Client list --}}
            <div class="col-9">
                <table class="table table-striped">

                    <body>
                        @foreach ($clients as $client)
                            @php
                                $phonesCount = $client->phones->count();
                            @endphp
                            <tr>
                                <td>
                                    <a class="mr-3" href="{{ route('client-edit', [$client]) }}">{{ $client->name }}</a>
                                    <br />
                                    <small><b>PN</b> {{ $client->personal_no }}</small>
                                    <small><b>CardNo</b> {{ $client->card_no }}</small>
                                    @if ($phonesCount > 0)
                                        <small><b>PHONES ({{ $phonesCount }})</b></small>
                                        @foreach ($client->phones as $i => $phone)
                                            @if ($i <= 1)
                                                <small><span class="text-info">{{ $i + 1 }})</span>
                                                    {{ $phone->number }}</small>
                                            @endif
                                        @endforeach
                                        @if ($phonesCount > 2)
                                            ...
                                        @endif
                                    @endif
                                </td>

                                <td>
                                    <a class="btn btn-sm btn-outline-danger mr-3"
                                        href="{{ route('client-delete', [$client]) }}">X</a><br />
                                    <small class="ml-3"><i>{{ $client->updated_at }}</i></small>
                                </td>
                            </tr>
                        @endforeach
                    </body>
                </table>
            </div>
        </div>
    </div>

@endsection
