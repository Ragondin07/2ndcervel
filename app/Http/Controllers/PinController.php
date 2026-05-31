<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\Decision;
use App\Models\File as StoredFile;
use App\Models\Note;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;

class PinController extends Controller
{
    /**
     * @var array<string, class-string<Model>>
     */
    private const SUPPORTED_TYPES = [
        'project' => Project::class,
        'note' => Note::class,
        'decision' => Decision::class,
        'action' => Action::class,
        'file' => StoredFile::class,
    ];

    public function __invoke(string $type, int $id): RedirectResponse
    {
        $model = self::SUPPORTED_TYPES[$type] ?? null;

        if ($model === null) {
            return back()->withErrors([
                'pin' => 'Ce type de favori n’est pas pris en charge.',
            ]);
        }

        $item = $this->findItem($model, $id);

        if (! Schema::hasColumn($item->getTable(), 'is_pinned')) {
            return back()->withErrors([
                'pin' => 'La colonne de favoris est absente. Executez les migrations puis reessayez.',
            ]);
        }

        $item->update(['is_pinned' => ! (bool) $item->getAttribute('is_pinned')]);

        return back()->with('status', $item->getAttribute('is_pinned') ? 'Element ajoute aux favoris.' : 'Element retire des favoris.');
    }

    /**
     * @param class-string<Model> $model
     */
    private function findItem(string $model, int $id): Model
    {
        return $model::query()->findOrFail($id);
    }
}
