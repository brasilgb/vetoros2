<?php

namespace App\Http\Concerns;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use LogicException;

trait HandlesDomainActions
{
    /**
     * Runs a domain action, redirecting back with a flash toast either way.
     * Domain rule violations (LogicException) never bubble up as a 500 page.
     */
    protected function attempt(callable $action, string $successMessage): RedirectResponse
    {
        try {
            $action();
        } catch (LogicException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => $successMessage]);

        return back();
    }
}
