<section>
    <div class="row g-4">
        <div style="margin-top: 40px" class="col-md-12 mb-12">
            <h5 class="fw-semibold mb-3"><i class="bi bi-calendar-event text-danger me-2"></i>Подходит
                для:</h5>
            @php
                $events = $subj['details_obj']['for_events'];
            @endphp
            <div class="d-flex flex-wrap align-items-start gap-2">
                @for ($i = 0; $i < count($events); $i++)
                    <span class="feature-badge">
                {{ $events[$i] }}
            </span>
                @endfor
            </div>
        </div>
        <div style="margin-top: 40px" class="col-md-12 mb-12">
            <h5 class="fw-semibold mb-3"><i class="bi bi-star text-info me-2"></i>Дополнительные услуги:
            </h5>
            @if(!empty($services))
                @php
                    $services = $subj['details_obj']['service'];
                @endphp
                <div class="d-flex flex-wrap align-items-start gap-2">
                    @for ($i = 0; $i < count($services); $i++)
                        <span class="feature-badge">
                {{ $services[$i] }}
            </span>
                    @endfor

                </div>
            @endif
        </div>
        @if(!empty($features))
            <div style="margin-top: 40px" class="col-md-12 mb-12">
                <h5 class="fw-semibold mb-3"><i class="bi bi-lightbulb text-success me-2"></i>Особенности:
                </h5>
                @php
                    $features = $subj['features'];
                @endphp
                <div class="d-flex flex-wrap align-items-start gap-2">
                    @for ($i = 0; $i < count($features); $i++)
                        <span class="feature-badge">
            {{ $features[$i] }}
        </span>
                    @endfor
                </div>
            </div>
        @endif
        <div style="margin-top: 40px" class="col-md-4 mb-4">
            <h5 class="fw-semibold mb-3"><i class="bi bi-credit-card text-dark me-2"></i>Способы
                оплаты:
            </h5>
            @php
                $payments = $subj['details_obj']['payment_methods'];
            @endphp
            <div>
                @for ($i = 0; $i < count($payments); $i++)
                    <span class="feature-badge">
                {{ $payments[$i] }}
            </span>
                @endfor
            </div>
        </div>
        <div style="margin-top: 40px" class="col-md-4 mb-4">
            <h5 class="fw-semibold mb-3"><i class="bi bi-wine text-danger me-2"></i>Алкоголь:</h5>
            @if($subj['details_obj']['alcohol'])
                <span class="feature-badge bg-success text-white">
            Разрешён
        </span>
            @else
                <span class="feature-badge bg-danger text-white">
            Не разрешён
        </span>
            @endif
        </div>
    </div>
</section>