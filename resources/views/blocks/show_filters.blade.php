<style>
    .applied-filters-badge {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        font-size: 14px;
        color: #2d3748;
        flex-wrap: wrap;
    }

    .applied-filters-badge .badge-label {
        font-weight: 600;
        color: #4a5568;
    }

    .applied-filters-badge .badge-values {
        color: #1e293b;
        white-space: nowrap;
    }

    /* На мобильных — переносим значения на новую строку, если не влезают */
    @media (max-width: 768px) {
        .applied-filters-badge {
            flex-direction: column;
            align-items: flex-start;
        }

        .applied-filters-badge .badge-values {
            width: 100%;
            word-break: break-word;
        }
    }

</style>


{{-- Плашка с активными фильтрами --}}
@if(!empty($appliedFilters))
    <div class="applied-filters-badge mb-3">
        <span class="badge-label">Активные фильтры:</span>
        <span class="badge-values">
                        {{ implode(' | ', $appliedFilters) }}
                    </span>
    </div>
@endif
{{-- Конец плашки --}}

