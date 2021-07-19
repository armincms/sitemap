<?php

namespace Armincms\Sitemap\Http\Controllers;
 
use Armincms\Targomaan\Contracts\Translatable; 
use Armincms\Targomaan\Contracts\Serializable;
use Illuminate\Http\Request; 
use Illuminate\Routing\Controller; 
use Laravel\Nova\Nova; 
use Laravel\Nova\Events\ServingNova;

class SitemapController extends Controller
{          
	/**
	 * Route of Component.
	 * 
	 * @var null
	 */
	protected $route = '{group}';

	public function handle(Request $request) 
	{   
		ServingNova::dispatch($request);
		$path = preg_match('/([^\.]+)-([0-9]+)/', $request->route('group'), $matches); 
		$resource = Nova::resourceForKey($matches[1]); 
		$offset = [$matches[2]*50000, ($matches[2]+1)*50000];
		$urls = collect();

		abort_if(is_null($resource), 404);

		$resource::newModel()
			->whereBetween($resource::newModel()->getKeyName(), $offset)
			->chunk(500, function($resources) use (&$urls) {
				$resources = $resources->each(function($resource) use (&$urls) {
					if ($resource instanceof Translatable && 
						$resource->translator() != 'sequential'
					) {
						$urls = $urls->merge($this->getMultilingualResourceUrls($resource)); 
						 return;
					}

					$urls->push([
						'lastmod' => strval($resource->updated_at),
						'url' => method_exists($resource, 'url') ? $resource->url() : $resource->url,
					]); 
				}); 
			}); 
 
		return response(view('sitemap::xml', compact('urls')), 200, [
		    'Content-Type' => 'application/xml'
		]);  
	} 

	protected function getMultilingualResourceUrls($resource)
	{  
		$urls = $resource instanceof Serializable 
			? $resource->getOriginal('url') 
			: $resource->translations->map->url;

		return collect($urls)->map(function($url) use ($resource) {
			return [
				'lastmod' => strval($resource->updated_at),
				'url' => $resource->site()->url(rawurldecode($url)),
			];
		});
	} 
}
