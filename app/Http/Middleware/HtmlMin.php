<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HtmlMin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
      if(env('MIN_HTML', true) === true){
        $response = $next($request);
        $buffer = $response->getContent();
        if(strpos($buffer,'<pre>') !== false)
        {
          $replace = array(
            '/<!--[^\[](.*?)[^\]]-->/s' => '',
            "/<\?php/"                  => '<?php ',
            "/\r/"                      => '',
            "/>\n</"                    => '><',
            "/>\s+\n</"                 => '><',
            "/>\n\s+</"                 => '><',
          );
        }
        else
        {
          $replace = array(
            '/<!--[^\[](.*?)[^\]]-->/s' => '',
            "/<\?php/"                  => '<?php ',
            "/\n([\S])/"                => '$1',
            "/\r/"                      => '',
            "/\n/"                      => '',
            "/\t/"                      => '',
            "/ +/"                      => ' ',
          );
        }
        $buffer = preg_replace(array_keys($replace), array_values($replace), $buffer);
        $response->setContent($buffer);
        return $response;
      }else{
        return $next($request);
      }
    }
}