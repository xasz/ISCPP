<?php

test('that true is true', function () {
    putenv("CACHE_STORE=file");
    expect(true)->toBeTrue();
});