@extends('layouts.app')
@section('content')
    <style>
        .restaurants-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 24px;
            padding: 20px;
        }

        .restaurant-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #f0f0f0;
        }

        .restaurant-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .restaurant-image {
            height: 220px;
            overflow: hidden;
            background: #f8f9fa;
        }

        .restaurant-image img,
        .restaurant-image .placeholder {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .placeholder {
            display: none;
            align-items: center;
            justify-content: center;
            background: #f0f2f5;
            color: #6c757d;
            font-size: 14px;
        }

        .restaurant-content {
            padding: 20px;
        }

        .restaurant-name {
            margin: 0 0 12px 0;
            color: #2c3e50;
            font-size: 20px;
            font-weight: 600;
        }

        .restaurant-features {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 16px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            color: #495057;
        }

        .feature-icon {
            font-size: 16px;
        }

        .tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 16px;
        }

        .tag {
            background: #e9ecef;
            color: #495057;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .features-list {
            margin-bottom: 16px;
        }

        .features-list strong {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
        }

        .features-list ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .features-list li {
            margin-bottom: 4px;
            color: #495057;
            font-size: 14px;
        }

        .restaurant-description {
            color: #6c757d;
            line-height: 1.5;
            font-size: 14px;
            margin-bottom: 16px;
            max-height: 80px;
            overflow: hidden;
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .restaurants-grid {
                grid-template-columns: 1fr;
                padding: 10px;
            }

            .restaurant-card {
                margin-bottom: 16px;
            }
        }
    </style>

    <div class="restaurants-grid">
        @foreach ($favorites as $restaurant)
            <div class="restaurant-card col-sm-12 col-md-6 col-lg-4">
                <!-- Изображение -->
                <div class="restaurant-image">
                    @if ($restaurant['primary_img'] && $restaurant['primary_img']['small_img'])
                        <img src="{{ $restaurant['primary_img']['small_img'] }}"
                             alt="Фото ресторана {{ $restaurant['name_subj'] }}"
                             onerror="this.style.display='none'; this.parentNode.querySelector('.placeholder').style.display='flex'">
                    @else
                        <div class="placeholder">
                            <span>Фото отсутствует</span>
                        </div>
                    @endif
                </div>

                <!-- Контент -->
                <div class="restaurant-content">
                    <!-- Название -->
                    <h3 class="restaurant-name">{{ $restaurant['name_subj'] }}</h3>

                    <!-- Характеристики -->
                    <div class="restaurant-features">
                        <!-- Вместимость -->
                        @if ($restaurant['capacity_to'])
                            <div class="feature-item">
                                <span class="feature-icon">👥</span>
                                <span>{{ $restaurant['capacity_to'] }} гостей</span>
                            </div>
                        @endif

                        <!-- Стоимость на человека -->
                        @if ($restaurant['per_person'])
                            <div class="feature-item">
                                <span class="feature-icon">💰</span>
                                <span>{{ number_format($restaurant['per_person'], 0, '', ' ') }} руб/чел</span>
                            </div>
                        @endif

                        <!-- Минимальный бюджет -->
                        @if ($restaurant['minimum_cost'])
                            <div class="feature-item">
                                <span class="feature-icon">💸</span>
                                <span>от {{ number_format($restaurant['minimum_cost'], 0, '', ' ') }} руб</span>
                            </div>
                        @endif
                    </div>

                    <!-- Типы площадок -->
                    @if (!empty($restaurant['site_type']))
                        <div class="tags">
                            @foreach ($restaurant['site_type'] as $type)
                                <span class="tag">{{ $type }}</span>
                            @endforeach
                        </div>
                    @endif

                    <!-- Особенности -->
                    @if (!empty($restaurant['features']))
                        <div class="features-list">
                            <strong>Особенности:</strong>
                            <ul>
                                @foreach ($restaurant['features'] as $feature)
                                    <li>✓ {{ $feature }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Описание -->
                    @if ($restaurant['text_subj'])
                        <div class="restaurant-description">
                            {!! nl2br(e($restaurant['text_subj'])) !!}
                        </div>
                    @endif
                    <center>
                        <a style="width: auto"
                           href="{{route('show.subj', ['id' => $restaurant['obj_id']])}}"
                           class="btn-festive-gradient btn-festive-gradient-green front-btn m-3">
                            Подробнее
                        </a>
                    </center>
                </div>
            </div>
        @endforeach
    </div>

@endsection
