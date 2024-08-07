<?PHP

	use Wing\Design\DesignCache;

	DesignCache::reset();
    DesignCache::syncCacheDomains(); //다른 웹서버 갱신 요청

?>