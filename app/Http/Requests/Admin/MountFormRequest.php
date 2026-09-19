<?php

namespace Pterodactyl\Http\Requests\Admin;

use Pterodactyl\Models\Mount;

class MountFormRequest extends AdminFormRequest
{
    /**
     * Set up the validation rules to use for these requests.
     */
    public function rules(): array
    {
        if ($this->method() === 'PATCH') {
            if ($this->input('action') === 'delete') {
                return [];
            }

            return Mount::getRulesForUpdate($this->route()->parameter('mount')->id);
        }

        return Mount::getRules();
    }
}
