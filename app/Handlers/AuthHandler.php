<?php
/**
 * Date 04/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Handlers;

use App\Models\User;
use DateTimeImmutable;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\JwtFacade;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\UnencryptedToken;

class AuthHandler
{
    protected SystemClock $clock;
    private Sha256 $signer;
    private InMemory $key;
    /** @var non-empty-string */
    private string $issuer;
    private string $permittedFor;

    public function __construct()
    {
        $this->clock = new SystemClock(new \DateTimeZone(config('app.timezone')));
        $this->signer = new Sha256();
        $this->key = InMemory::base64Encoded(config('jwt.key'));
        $this->issuer = config('app.url');
        $this->permittedFor = config('app.url');
    }

    public function generateToken(User $user): UnencryptedToken
    {
        $issuer = $this->issuer;
        $permittedFor = $this->permittedFor;
        return (new JwtFacade())->issue(
            $this->signer,
            $this->key,
            static fn(
                Builder $builder, DateTimeImmutable $issuedAt
            ): Builder => $builder->issuedBy($issuer)
                                ->identifiedBy(uniqid())
                                ->withClaim('email', $user->email)
                                ->withClaim('uuid', $user->uuid)
                                ->permittedFor($permittedFor)
                                ->expiresAt(
                                    $issuedAt->modify('+30 minutes')
                                        ->setTimezone(new \DateTimeZone(config('app.timezone')))
                                )
        );
    }
}
