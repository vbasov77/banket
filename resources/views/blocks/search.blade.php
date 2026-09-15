<style>
    .yandex-search-wrapper {
        display: flex;
        align-items: center;
        max-width: 600px;
        margin: 0 auto;
    }

    .yandex-search-form {
        display: flex;
        width: 100%;
        position: relative;
    }

    .yandex-search-input {
        width: 100%;
        padding: 10px 44px 10px 16px;
        font-size: 15px;
        line-height: 1.4;
        border: 1px solid #dcdcdc;
        border-radius: 28px;
        background-color: #fff;
        color: #000;
        box-sizing: border-box;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .yandex-search-input:focus {
        border-color: #1a73e8;
        box-shadow: 0 0 0 2px rgba(26, 115, 232, 0.2);
    }

    .yandex-search-btn {
        position: absolute;
        right: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        padding: 0;
        border: none;
        background: transparent;
        cursor: pointer;
        color: #5f5f5f;
        border-radius: 50%;
    }

    .yandex-search-btn:hover {
        background-color: #f0f0f0;
        color: #000;
    }

    .yandex-search-btn svg {
        display: block;
    }

</style>


<div class="yandex-search-wrapper p-2">
    <form action="{{ route('search.by_name') }}" method="GET" class="yandex-search-form">
        <button type="submit" class="yandex-search-btn" aria-label="Найти">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
        </button>

        <input
                type="text"
                name="q"
                id="search-input"
                class="yandex-search-input"
                placeholder="Поиск по названию площадки…"
                value="{{ request('q', '') }}"
                autocomplete="off"
                aria-autocomplete="list"
        >

        <ul id="search-suggestions" class="search-suggestions-list" style="display:none; position:absolute; top:100%; left:0; width:100%; max-height:200px; overflow-y:auto; margin:0; padding:0; border:1px solid #dcdcdc; border-top:none; background:#fff; z-index:1050; list-style:none; box-shadow:0 4px 6px rgba(0,0,0,.1);">
            <!-- сюда JS будет вставлять li -->
        </ul>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('search-input');
        const suggestionsList = document.getElementById('search-suggestions');
        const routeSuggestions = "{{ route('search.suggestions') }}";

        function debounce(fn, delay) {
            let timer;
            return function (...args) {
                clearTimeout(timer);
                timer = setTimeout(() => fn.apply(this, args), delay);
            };
        }

        const fetchSuggestions = async (query) => {
            if (!query) {
                suggestionsList.style.display = 'none';
                return;
            }

            try {
                const response = await fetch(`${routeSuggestions}?q=${encodeURIComponent(query)}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        // Если нужны CSRF-токены для GET — можно добавить, но для GET обычно не требуется
                    },
                });

                if (!response.ok) {
                    suggestionsList.style.display = 'none';
                    return;
                }

                const data = await response.json();
                const matches = data.suggestions || [];

                suggestionsList.innerHTML = '';

                if (matches.length === 0) {
                    suggestionsList.style.display = 'none';
                    return;
                }

                matches.forEach(item => {
                    const li = document.createElement('li');
                    li.style.padding = '8px 12px';
                    li.style.cursor = 'pointer';
                    li.style.borderBottom = '1px solid #f0f0f0';
                    li.textContent = item.name;

                    li.addEventListener('click', () => {
                        input.value = item.name;
                        suggestionsList.style.display = 'none';
                        input.form?.submit?.(); // отправить форму
                    });

                    suggestionsList.appendChild(li);
                });

                suggestionsList.style.display = 'block';
            } catch (e) {
                console.error('Search suggestions error', e);
                suggestionsList.style.display = 'none';
            }
        };

        const showSuggestions = debounce((query) => fetchSuggestions(query), 200);

        input.addEventListener('input', (e) => showSuggestions(e.target.value));

        // Закрывать список при клике вне
        document.addEventListener('click', (e) => {
            if (!suggestionsList.contains(e.target) && !input.contains(e.target)) {
                suggestionsList.style.display = 'none';
            }
        });

        // Закрыть по Escape
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                suggestionsList.style.display = 'none';
            }
        });
    });
</script>
