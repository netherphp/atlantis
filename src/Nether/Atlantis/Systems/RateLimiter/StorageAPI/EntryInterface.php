<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Systems\RateLimiter\StorageAPI;


################################################################################
################################################################################

interface EntryInterface {

	public function
	Bump(int $Inc): int;

	public function
	Delete(): void;

	////////

	public function
	GetHitHash(): string;

	public function
	GetTimeLogged(): int;

	public function
	GetAttemptsRemaining(): int;

};
