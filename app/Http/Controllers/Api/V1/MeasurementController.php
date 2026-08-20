<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMeasurementRequest;
use App\Http\Resources\MeasurementResource;
use App\Models\MeasurementProfile;
use Illuminate\Http\Request;

class MeasurementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $profiles = MeasurementProfile::with('user')
            ->when($user->isCustomer(), fn ($q) => $q->where('user_id', $user->id))
            ->when($user->isTailor(), fn ($q) => $q->whereIn('user_id', $user->assignedOrders()->pluck('user_id')))
            ->when(!$user->isCustomer() && !$user->isTailor(), fn ($q) => $q)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success(MeasurementResource::collection($profiles), 'Success');
    }

    public function store(StoreMeasurementRequest $request)
    {
        $profile = MeasurementProfile::create(array_merge($request->validated(), [
            'user_id' => $request->user()->id,
            'name' => $request->validated('name') ?? 'My Measurements',
        ]));

        return $this->success(new MeasurementResource($profile), 'Measurement profile created', 201);
    }

    public function show(Request $request, MeasurementProfile $profile)
    {
        $this->authorize('view', $profile);

        return $this->success(new MeasurementResource($profile), 'Success');
    }

    public function update(StoreMeasurementRequest $request, MeasurementProfile $profile)
    {
        $this->authorize('update', $profile);

        $profile->update($request->validated());

        return $this->success(new MeasurementResource($profile), 'Measurement profile updated');
    }

    public function destroy(Request $request, MeasurementProfile $profile)
    {
        $this->authorize('delete', $profile);

        $profile->delete();

        return $this->success(null, 'Measurement profile deleted');
    }
}
