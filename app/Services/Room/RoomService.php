<?php

namespace App\Services\Room;

use App\Models\Room;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class RoomService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Room::query();

        $query->when(
            $filters['search'] ?? null,
            function (Builder $query, string $search): void {
                $query->where(function (Builder $q) use ($search): void {
                    $q->where('name_km', 'ilike', "%{$search}%")
                        ->orWhere('name_en', 'ilike', "%{$search}%")
                        ->orWhere('building_km', 'ilike', "%{$search}%")
                        ->orWhere('building_en', 'ilike', "%{$search}%");
                });
            }
        );

        $query->when(
            $filters['status'] ?? null,
            fn(Builder $query, string $status) => $query->where('status', $status)
        );

        return $query
            ->orderBy('name_km')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data): Room
    {
        return DB::transaction(function () use ($data): Room {
            return Room::create($data);
        });
    }

    public function findById(int $id): Room
    {
        return Room::query()->findOrFail($id);
    }

    public function update(Room $room, array $data): Room
    {
        return DB::transaction(function () use ($room, $data): Room {
            $room->update($data);

            return $room->refresh();
        });
    }

    public function delete(Room $room): void
    {
        DB::transaction(function () use ($room): void {
            $room->delete();
        });
    }
}
