@extends('layouts.app')

@section('content')
    <style>
        .favorites-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 24px;
        }

        .fav-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid #f0f0f0;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            display: flex;
            flex-direction: column;
        }

        .fav-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .fav-card-img {
            height: 220px;
            overflow: hidden;
            background: #f8f9fa;
        }

        .fav-card-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .fav-card-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f2f5;
            color: #6c757d;
            font-size: 14px;
        }

        .fav-card-body {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .fav-card-title {
            margin: 0 0 12px;
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
        }

        .fav-features {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 16px;
        }

        .fav-feature {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            color: #495057;
        }

        .fav-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 16px;
        }

        .fav-tag {
            background: #e9ecef;
            color: #495057;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .fav-card-footer {
            margin-top: auto;
            text-align: center;
        }

        @media (max-width: 768px) {
            .favorites-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="container py-4">
        <h2 class="mb-4 text-center">Избранное</h2>

        <div class="row">
            <div class="col-12">
                @if (!empty($favorites))
                    <div class="favorites-grid">
                        @foreach ($favorites as $item)
                            <div class="fav-card">
                                <div class="fav-card-img">
                                    @if (!empty($item['primary_img']['small_img']))
                                        <img src="{{ $item['primary_img']['small_img'] }}"
                                             alt="{{ $item['name_subj'] }}"
                                             onerror="this.style.display='none'; this.parentNode.querySelector('.fav-card-placeholder').style.display='flex'">
                                    @else
                                        <div class="fav-card-placeholder">Фото отсутствует</div>
                                    @endif
                                </div>

                                <div class="fav-card-body">
                                    <h3 class="fav-card-title">{{ $item['name_subj'] }}</h3>

                                    <div class="fav-features">
                                        @if (!empty($item['capacity_to']))
                                            <div class="fav-feature">
                                                <span>👥</span>
                                                <span>{{ $item['capacity_to'] }} гостей</span>
                                            </div>
                                        @endif
                                        @if (!empty($item['per_person']))
                                            <div class="fav-feature">
                                                <span>💰</span>
                                                <span>{{ number_format($item['per_person'], 0, '', ' ') }} руб/чел</span>
                                            </div>
                                        @endif
                                        @if (!empty($item['minimum_cost']))
                                            <div class="fav-feature">
                                                <span>💸</span>
                                                <span>от {{ number_format($item['minimum_cost'], 0, '', ' ') }} руб</span>
                                            </div>
                                        @endif
                                    </div>

                                    @if (!empty($item['site_type']))
                                        <div class="fav-tags">
                                            @foreach ($item['site_type'] as $type)
                                                <span class="fav-tag">{{ $type }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="fav-card-footer">
                                        <a href="{{ route('show.subj', ['id' => $item['id']]) }}"
                                           class="btn-festive-gradient btn-festive-gradient-green front-btn">
                                            Подробнее
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-muted py-5">Список избранного пуст</p>
                @endif
            </div>
        </div>
    </div>
@endsection
