<form method="post" action="{{ route('inbox.archive', ['type' => $type, 'id' => $id]) }}">
    @csrf
    @method('patch')
    <button class="secondary" type="submit">Archiver</button>
</form>
<form method="post" action="{{ route('inbox.destroy', ['type' => $type, 'id' => $id]) }}" onsubmit="return confirm('Supprimer definitivement cet element ?');">
    @csrf
    @method('delete')
    <button class="danger" type="submit">Supprimer</button>
</form>
