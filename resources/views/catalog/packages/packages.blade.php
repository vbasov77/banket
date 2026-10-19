@extends('layouts.app', ['title' => 'Пакеты услуг — ' . $performer->name])

@section('content')
    <style>
        @media (max-width: 575.98px) {
            .btn-festive-gradient {
                width: 100%;
                justify-content: center;
                text-align: center;
            }
        }

    </style>
    <div class="container mt-4" style="padding-bottom: 50px">
        <div class="row justify-content-center">
            <div class="col-lg-9 col-md-12">

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/">Главная</a></li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('catalog.request.my_category') }}">Мои заявки</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('catalog.request.edit', $performer) }}">Редактирование</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Пакеты услуг</li>
                    </ol>
                </nav>

                <h1 class="h4 fw-bold mb-1">Пакеты услуг</h1>
                <p class="text-muted mb-4">{{ $performer->name }}</p>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Закрыть"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Проверьте форму — есть ошибки.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Закрыть"></button>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST"
                      action="{{ route('catalog.packages.update', [$category->slug, $performer->id]) }}">
                    @csrf

                    <div id="packages-container">
                        @include('catalog.packages._package_rows', ['rows' => $packageItems])
                    </div>

                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-package">
                        <i class="bi bi-plus-lg me-1"></i>Добавить пакет
                    </button>

                    <div class="d-flex flex-column flex-sm-row gap-2 mt-4">
                        <button type="submit" class="btn-festive-gradient btn-festive-gradient-green">
                            Сохранить
                        </button>
                        <button type="button" class="btn-festive-gradient btn-festive-gradient-white"
                                onclick="history.back()">
                            Назад
                        </button>
                        <a href="{{ route('catalog.request.my_category') }}"
                           class="btn-festive-gradient btn-festive-gradient-blue">Панель</a>
                    </div>

                </form>

                {{-- Шаблон для JS — рендерится один раз, в DOM не вставляется --}}
                <template id="package-template">
                    @include('catalog.packages._package_row', ['i' => 'NEW_INDEX', 'pkg' => [
                        'title'       => '',
                        'description' => '',
                        'price_min'   => '',
                        'price_max'   => '',
                        'is_addon'    => false,
                    ]])
                </template>
            </div>
        </div>
    </div>

    <script>
        const container = document.getElementById('packages-container');
        const template = document.getElementById('package-template');

        // Пересчёт имён полей по порядку строк в DOM.
        // Значения не трогаем — они «переезжают» вместе с инпутами,
        // поэтому перестановка/удаление не ломают данные.
        function renumber() {
            container.querySelectorAll('.package-row').forEach((row, i) => {
                row.querySelectorAll('input, textarea').forEach(el => {
                    const field = el.dataset.field;
                    el.name = `packages[${i}][${field}]`;
                });
                row.querySelector('.package-num').textContent = i + 1;
            });
        }

        document.getElementById('add-package').addEventListener('click', () => {
            container.insertAdjacentHTML('beforeend', template.innerHTML);
            renumber();
        });

        container.addEventListener('click', (e) => {
            const row = e.target.closest('.package-row');
            if (!row) return;

            if (e.target.closest('.package-remove')) {
                row.remove();
                renumber();
            }
            if (e.target.closest('.package-up')) {
                row.previousElementSibling?.before(row);
                renumber();
            }
            if (e.target.closest('.package-down')) {
                row.nextElementSibling?.after(row);
                renumber();
            }
        });

        renumber(); // на случай, если строка добавлена, а индексы съехали
    </script>
@endsection
