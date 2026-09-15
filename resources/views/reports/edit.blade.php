@extends('layouts.app')
@section('content')

    <section class="about-section text-center" id="about">
        <div class="container px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5 justify-content-center">
                <div class="col-lg-8">
                    <form action="{{route("admin.update.report")}}" method="post">
                        @csrf
                        <h3> Изменить данные</h3>
                        <br>
                        <input type="hidden" name="id" value="{{$report->id}}">
                        <div class="size">
                            <label for="expenses"><b>Затраты:</b></label>
                            <input   name="expenses" type="number" class="form-control"
                                     value="{{$report->expenses}}"
                                     placeholder="Введите сумму..." autocomplete="off" required>
                        </div>
                        <br>
                        <div class="size">
                            <label for="info_expenses"><b>Описание:</b></label>
                            <br>
                            <small><i>Через запятую</i></small>
                            <input   name="info_expenses" type="text" class="form-control"
                                     value="{{$report->info_expenses}}"
                                     placeholder="Описание затрат..." autocomplete="off" required>
                        </div>
                        <div>
                            <input class="btn btn-outline-success" type="submit" value="Изменить">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection
