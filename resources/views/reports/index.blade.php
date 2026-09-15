@extends('layouts.app')
@section('content')
    <style>
        .custom-tooltip .tooltip-text {
            visibility: hidden;
            position: absolute;
            background-color: black;
            color: white;
            font-family: emoji;
            padding: 10px;
        }

        @media (max-width: 600px) {
            .indexTable {
                font-size: 7px;
            }

            .mob {
                display: none;
            }
        }


        .custom-tooltip:hover .tooltip-text {
            visibility: visible; /* Вот такое чудо! */
        }
    </style>

    <section class="about-section text-center" id="about">
        <div class="container-fluid">
            <div class="row gx-4 gx-lg-5 justify-content-center">
                <div class="col-lg-9 indexTable">
                    <br>
                    <canvas id="weekday" width="600" height="300"></canvas>
                    <br>
                    <br>
                    <a href="{{route('reports.clear')}}" class="btn btn-outline-danger btn-sm">
                        Очистить базу
                    </a>

                </div>
            </div>
        </div>
    </section>
    @push('scripts')
        <script>
            var week = @json($week);
            var dataWeek = @json($dataWeek);
        </script>
        <script src="{{ asset('js/chart/chart.min.js') }}" defer></script>
        <script src="{{ asset('js/chart/weekday.js') }}" defer></script>
    @endpush
@endsection
