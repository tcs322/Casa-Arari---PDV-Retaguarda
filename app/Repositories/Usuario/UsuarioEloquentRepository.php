<?php

namespace App\Repositories\Usuario;

use App\DTO\Usuario\UsuarioResetDTO;
use App\DTO\Usuario\UsuarioStoreDTO;
use App\DTO\Usuario\UsuarioUpdateDTO;
use App\Enums\MustChangePasswordEnum;
use App\Helpers\PasswordHelper;
use App\Models\User;
use App\Repositories\Interfaces\PaginationInterface;
use App\Repositories\Presenters\PaginationPresenter;
use Illuminate\Database\Eloquent\Collection;

class UsuarioEloquentRepository implements UsuarioRepositoryInterface
{
    public function __construct(
        protected User $model
    ){ }

    public function all(array $filters): array
    {
        return $this->model->all()->toArray();
    }

    public function totalQuantity() : int {
        return $this->model->count();
    }

    public function new(UsuarioStoreDTO $usuarioStoreDTO): User
    {
        return $this->model->create((array)$usuarioStoreDTO);
    }

    public function find(string $uuid): User
    {
        return $this->model->where("uuid", $uuid)->first();
    }

    public function paginate(int $page = 1, int $totalPerPage = 15, string $filter = null): PaginationInterface
    {
        $query = $this->model->query();

        if (!is_null($filter)) {
            $query->where("name", "like", "%".$filter."%");
            $query->orWhere("email", "like", "%".$filter."%");
        }

        $query->orderBy('updated_at', 'desc');

        $result = $query->paginate($totalPerPage, ['*'], 'page', $page);

        return new PaginationPresenter($result);
    }

    public function update(UsuarioUpdateDTO $usuarioUpdateDTO): User
    {
        $this->model->where("uuid", $usuarioUpdateDTO->uuid)->update((array)$usuarioUpdateDTO);

        return $this->find($usuarioUpdateDTO->uuid);
    }

    public function reset(UsuarioResetDTO $usuarioResetDTO): User
    {
        $user = $this->find($usuarioResetDTO->uuid);

        $user->update(
            [
                "password" => PasswordHelper::generateTemporaryPassword($randomPassword = false),
                "must_change_password" => MustChangePasswordEnum::YES()->value,
            ]
        );

        return $this->find($usuarioResetDTO->uuid);
    }
}
