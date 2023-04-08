<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\JwtFacade;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    /**
     * @var \Lcobucci\JWT\Signer\Key\InMemory
     */
    protected InMemory $key;
    /**
     * @var \Lcobucci\JWT\Signer\Hmac\Sha256
     */
    protected Sha256 $signer;
    /**
     * @var \Lcobucci\Clock\SystemClock
     */
    protected SystemClock $clock;
    /**
     * @var \Lcobucci\JWT\Validation\Constraint\SignedWith
     */
    protected SignedWith $signedWith;

    public function __construct()
    {
        $this->signer = new Sha256();
        $this->key = InMemory::base64Encoded(config('jwt.key'));
        $this->clock = new SystemClock(new \DateTimeZone(config('app.timezone')));
        $this->signedWith = new SignedWith($this->signer, $this->key);
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$beareToken = $request->bearerToken()) {
            // Unauthorized response if token not there
            return response()->json([
                'error' => 'You are not authorized to access this resource!'
            ], 403);
        }

        try {
            $token = (new JwtFacade())->parse(
                $beareToken,
                $this->signedWith,
                new StrictValidAt( $this->clock ),
                new LooseValidAt( $this->clock )
            );
            // $request->request->add(['uuid' => $token->claims()->get('uuid')]);

            return $next($request);

        } catch (\Exception $exception) {
            $response = [
                'success' => false,
                'data' => [],
                'error' => 'Invalid Token! You are not authorized to access this resource!',
                'errors' => [],
                'trace' => [],
            ];
            return response()->json($response, 401);
        }

    }
}
