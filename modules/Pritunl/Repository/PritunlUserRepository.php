<?php

namespace Modules\Pritunl\Repository;

use Modules\Pritunl\Models\Enum\PritunlUserStatus;
use Modules\Pritunl\Models\PritunlUser;

class PritunlUserRepository
{
    public function __construct(protected PritunlUser $pritunlUser)
    {
    }

    public function updateToInUse(string $pritunl_user_id): PritunlUser|null
    {
        $pritunlUser = $this->pritunlUser->where("id", $pritunl_user_id)->first();

        if (!$pritunlUser) {

            return null;
        }

        $pritunlUser->update(["status" => PritunlUserStatus::IN_USE]);

        return $pritunlUser;
    }
}
