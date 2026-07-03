@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 mt-5 mb-5">
                <div class="card border-danger shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">Ошибка</h4>
                    </div>
                    <div class="card-body">
                        <p class="lead text-danger">
                           Субъект снят с публикации
                        </p>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
