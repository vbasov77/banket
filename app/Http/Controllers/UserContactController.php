<?php

namespace App\Http\Controllers;

use App\Models\UserContact;
use App\Services\ContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class UserContactController extends Controller
{
    /**
     * @param ContactService $contactService
     */
    public function __construct(private ContactService $contactService)
    {
    }


    /**
     * @return View|RedirectResponse
     */
    public function index(): View|RedirectResponse
    {
        try {
            // отдаём все контакты пользователя разом
            $contacts = auth()->user()->contacts()->get();

            return view('profile.contacts.index', ['contacts' => $contacts]);
        } catch (Throwable $e) {
            return $this->handleError($e, __METHOD__);
        }
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function sync(Request $request): RedirectResponse
    {
        try {
            $data = $request->validate([
                'whatsapp' => ['nullable', 'string', 'max:255'],
                'telegram' => ['nullable', 'string', 'max:255'],
                'max'      => ['nullable', 'string', 'max:255'],
                'vk'       => ['nullable', 'string', 'max:255'],
            ]);

            $this->contactService->sync($request->user(), $data);

            return redirect()
                ->route('contacts.index')
                ->with('success', 'Контакты сохранены');
        } catch (Throwable $e) {
            return $this->handleError($e, __METHOD__, $request->only(array_keys(UserContact::TYPES)));
        }
    }

    /**
     * @param Throwable $e
     * @param string $method
     * @param array $context
     * @return RedirectResponse
     */
    private function handleError(Throwable $e, string $method, array $context = []): RedirectResponse
    {
        Log::channel('error_file')->error([
            'message'   => $e->getMessage(),
            'exception' => get_class($e),
            'method'    => $method,
            'user_id'   => auth()->id(),
            ...$context,
            'trace'     => collect($e->getTrace())->take(5)->toArray(),
        ]);

        return redirect()
            ->route('contacts.index')
            ->with('error', 'Не удалось сохранить контакты. Попробуйте позже.');
    }
}
