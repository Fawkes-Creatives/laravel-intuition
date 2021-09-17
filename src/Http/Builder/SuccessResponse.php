<?php
/**
 * @author fawkescreatives created on 15/09/2021
 */

namespace ApiResponse\Formatter\Http\Builder;

use ApiResponse\Formatter\Helpers\ArrayService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SuccessResponse extends ResponseBuilder
{
    /**
     * @param null $data
     * @param mixed ...$parameters
     * @return array|array[]|LengthAwarePaginator[]|null[]
     */
    function build($data = null, ...$parameters)
    {
        return $this->setData($data)
                    ->setParameters(...$parameters)
                    ->render();
    }

    /**
     * @return array|array[]|LengthAwarePaginator[]|Arrayable[]|null[]|ResourceCollection
     */
    protected function render()
    {
        $dataWrapping = in_array(self::__DATA_WRAPPING, $this->getEnabledKeys());

        if (is_null($this->getData()) && !$dataWrapping) {
            return $this->getParameters();
        }

        if (is_null($this->getData()) && $dataWrapping) {
            return array_merge($this->getParameters(), [
                'data' => $this->getData()
            ]);
        }

        if (!ArrayService::isMultiDimensional($this->getData()) && !$dataWrapping) {
            return array_merge($this->getParameters() ?: [], $this->getData());
        }

        if ($this->getData() instanceof AnonymousResourceCollection ||
            $this->getData() instanceof ResourceCollection) {
            return $this->getData()->additional($this->getParameters());
        }

        if (!is_array($this->getData()) &&
            get_class($this->getData()) == 'Spatie\Fractal\Fractal') {
            return array_merge($this->getParameters(), $this->getData()->toArray());
        }

        if ($this->getData() instanceof LengthAwarePaginator) {
            return array_merge($this->getData()->toArray(), $this->getParameters());
        }

        return array_merge($this->getParameters(), [
            'data' => $this->getData()
        ]);
    }
}
