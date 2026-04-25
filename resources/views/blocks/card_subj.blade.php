<style>
    .restaurant-card.nearestObjects {
        border-radius: 10px;
    }

    .restaurant-image.nearestObjects {
        height: 150px;
    }

    .restaurant-content.nearestObjects {
        padding: 10px;
    }

    @media (max-width: 992px) {
        .restaurant-card {
            margin-top: 30px;
        }
    }

    @media (max-width: 768px) {
        .restaurant-card {
            margin-top: 5px;
        }
    }
</style>
@if(!empty($nearestObjects) && count($nearestObjects) > 0)
    <section class="mt-5">
        <h3 class="section-title fs-4 mb-4">Рестораны поблизости</h3>
    </section>
    <div class="row">
        @foreach($nearestObjects as $value)
            <div class="col-sm-12 col-md-6 col-lg-3">
                <div class="restaurant-card nearestObjects"
                     data-id="{{ $value['obj_id'] }}">
                    <!-- Изображение -->
                    <div class="position-relative">
                        <div class="restaurant-image nearestObjects">
                            <a href="{{route('group.address.show', ['id' => $value['group_id']])}}">
                                @if($value['path'])
                                    <img src="{{ $value['path'] . '&cs=360x0' }}"
                                         class="card-img-top" alt="Фото субъекта"
                                         style="object-fit: cover;">
                                @else
                                    <img src="{{ asset('images/no_image/no_image.jpg') }}"
                                         class="card-img-top" alt="Нет фото"
                                         style="height: 200px; object-fit: cover;">
                                @endif
                            </a>
                        </div>
                    </div>
                    <!-- Тело карточки -->
                    <div class="restaurant-content nearestObjects">
                        <!-- Название -->
                        <h5 class="card-title fw-bold mb-3">{{ $value['name_obj'] }}</h5>

                        <!-- Характеристики -->
                        <div class="details-info">
                            <!-- Вместимость -->
                            <div class="detail">
                                <span class="detail-label">Адрес:</span>
                                <span class="detail-value">{{ $value['address'] }}</span>
                            </div>
                            <div class="detail">
                                <span class="detail-label">Расстояние:</span>
                                <span class="detail-value">{{ $value['distance_km'] }} км.</span>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
