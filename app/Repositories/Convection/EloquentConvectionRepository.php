<?php

namespace App\Repositories\Convection;

use App\Models\Convection;
use App\Repositories\Convection\ConvectionRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class EloquentConvectionRepository implements ConvectionRepositoryInterface
{
    public function create(array $data)
    {
        return Convection::create($data);
    }

    public function update(string $convectionId, array $data)
    {
        $convection = $this->find($convectionId);
        $convection->update($data);
        $convection->refresh();
        return $convection;
    }

    public function find(string $convectionId)
    {
        return Convection::findOrFail($convectionId);
    }

    public function findAll()
    {
        return Convection::orderBy('created_at', 'desc')->get();
    }



    // public function findByParameters(array $parameters)
    // {
    //     $query = convection::query();

    //     foreach ($parameters as $key => $value) {
    //         $query->where($key, $value);
    //     }

    //     return $query->get();
    // }

    public function delete(string $convectionId)
    {
        $convection = $this->find($convectionId);
        $convection->delete();
    }

    public function banConvection($convectionId)
    {
        $convection = $this->find($convectionId);
        $convection->ban();
    }

    public function unBanConvection(string $convectionId)
    {
        $convection = $this->find($convectionId);
        $convection->unban();
    }
}
