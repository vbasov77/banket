<link href="{{ asset('css/subj/card_subj.css') }}" rel="stylesheet">

@if(!empty($nearestObjects) && count($nearestObjects) > 0)
    <section class="mt-5">
        <h3 class="section-title fs-4 mb-4">Рестораны поблизости</h3>
    </section>
    @php
        $countObj = count($nearestObjects);
    @endphp
    @for($i = 0; $i < $countObj; $i++)
        <div class="row">
            @foreach($nearestObjects as $value)
                <div class="col-12 col-sm-8 col-md-6 col-lg-4 restaurants-grid">
                    <div class="restaurant-card opacity"
                         data-id="{{ $value['obj_id'] }}">
                        <!-- Изображение -->
                        <div class="position-relative">
                            <div class="restaurant-image">
                                @if($value['photo'])
                                    <img src="{{ $value['photo'] . '&cs=360x0' }}"
                                         class="card-img-top" alt="Фото субъекта"
                                         style="height: 200px; object-fit: cover;">
                                @else
                                    <img src="{{ asset('images/no_image/no_image.jpg') }}"
                                         class="card-img-top" alt="Нет фото"
                                         style="height: 200px; object-fit: cover;">
                                @endif
                            </div>
                        </div>
                        <!-- Тело карточки -->
                        <div class="restaurant-content">
                            <!-- Название -->
                            <h5 class="card-title fw-bold mb-3">{{ $value['name_obj'] }}</h5>

                            <!-- Характеристики -->
                            <div class="details-info">
                                <!-- Вместимость -->
                                <div class="detail">
                                    <span class="detail-label">Район:</span>
                                    <span class="detail-value">{{ $value['address']['district_name'] }}</span>
                                </div>
                                <div class="detail">
                                    <span class="detail-label">Адрес:</span>
                                    <span class="detail-value">{{ $value['address']['address'] }}</span>
                                </div>
                                <div class="detail">
                                    <img src="{{ asset('icons/user.svg') }}"
                                         class="detail-label" style="width: 16px; height: 16px;"
                                         alt="Вместимость">
                                    <span class="detail-value">{{ $value['subj']['capacity_to'] }} мест</span>
                                </div>

                                <!-- Стоимость -->
                                @if(!empty($value['subj']['per_person']))
                                    <div class="detail">
                                        <img src="{{ asset('icons/ruble.svg') }}"
                                             class="detail-label" style="width: 16px; height: 16px;"
                                             alt="Рубль">
                                        <span class="detail-value price">От {{ number_format($value['subj']['per_person'], 0, ' ', ' ') }} ₽</span>
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endfor
@endif
