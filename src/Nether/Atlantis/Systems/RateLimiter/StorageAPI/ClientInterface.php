<?php ##########################################################################
################################################################################

namespace Nether\Atlantis\Systems\RateLimiter\StorageAPI;

################################################################################
################################################################################

interface ClientInterface {

	public function
	Bump(EntryInterface $Entry, int $Inc):
	static;

	public function
	Delete(EntryInterface $Entry):
	static;

	public function
	Fetch(string $HitHash):
	?EntryInterface;

	public function
	Touch(string $HitHash, int $MaxAttempts):
	EntryInterface;

};
