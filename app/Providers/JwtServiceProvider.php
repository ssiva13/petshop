<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\JwtFacade;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;

class JwtServiceProvider extends ServiceProvider
{
    protected SystemClock $clock;
    protected Sha256 $signer;
    protected InMemory $key;
    /** @var non-empty-string */
    protected string $issuer;
    protected string $permittedFor;
    protected SignedWith $signedWith;

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(Parser::class, function ($app, $bearer) {
           return (new JwtFacade())->parse(
                $bearer['token'],
                $this->signedWith,
                new StrictValidAt( $this->clock ),
                new LooseValidAt( $this->clock )
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->clock = new SystemClock(new \DateTimeZone(config('app.timezone')));
        $this->signer = new Sha256();
        $this->key = InMemory::base64Encoded(config('jwt.key'));
        $this->issuer = config('app.url');
        $this->permittedFor = config('app.url');

        $this->signedWith = new SignedWith($this->signer, $this->key);
    }
}
