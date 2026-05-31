<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\Decision;
use App\Models\File as StoredFile;
use App\Models\Note;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PinController extends Controller
{
    public function __invoke(string $type, int $id): RedirectResponse
    {
        $item = $this->findItem($type, $id);
        $item->update(['is_pinned' => ! (bool) $item->getAttribute('is_pinned')]);

        return back()->with('status', $item->getAttribute('is_pinned') ? 'Element ajoute aux favoris.' : 'Element retire des favoris.');
    }

    private function findItem(string $type, int $id): Model
    {
        $model = match ($type) {
            'project' => Project::class,
            'note' => Note::class,
            'decision' => Decision::class,
            'action' => Action::class,
            'file' => StoredFile::class,
            default => throw new NotFoundHttpException(),
        };

        return $model::query()->findOrFail($id);
    }
}
