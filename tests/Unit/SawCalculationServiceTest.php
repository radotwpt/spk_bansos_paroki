<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\SawCalculationService;

class SawCalculationServiceTest extends TestCase
{
    public function test_mapping_scores()
    {
        $s = new SawCalculationService();
        $this->assertEquals(3.0, $s->mapTempatTinggiToScore('numpang'));
        $this->assertEquals(2.0, $s->mapTempatTinggiToScore('sewa'));
        $this->assertEquals(1.0, $s->mapTempatTinggiToScore('milik_sendiri'));

        $this->assertEquals(3.0, $s->mapStatusHubunganToScore('cerai'));
        $this->assertEquals(2.0, $s->mapStatusHubunganToScore('menikah'));
        $this->assertEquals(1.0, $s->mapStatusHubunganToScore('lajang'));
    }
}
