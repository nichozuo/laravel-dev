<?php


namespace LaravelDev\App\Traits;


use Illuminate\Support\Str;

trait TestCaseTrait
{
    protected array $token = [];
    protected string $url = '';
    protected int $id;
    protected array $headers = [];

    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @intro 发起接口的请求
     * @param string $method
     * @param array $params
     * @param array $headers
     * @return void
     */
    protected function go(string $method, array $params = [], array $headers = []): void
    {
        list($module, $url) = $this->getUrl($method);
        $headers['Authorization'] = 'Bearer ' . $this->token[$module] ?? '';
        $url = $this->url ? $this->url . $url : $url;
        $response = $this->withHeaders($this->headers)->post($url, $params, $headers);
        $json = $response->json();
//        $this->assertTrue($response->getStatusCode() == 200);
        dump(json_encode($json));
        dump($json);
    }

    /**
     * @param string $method
     * @return array
     */
    private function getUrl(string $method): array
    {
        $t1 = explode('\\', $method);
        $urls[] = 'api';
        foreach ($t1 as $key => $value) {
            if ($key > 1 && $key < count($t1) - 1) {
                $urls[] = Str::snake($value);
            }
        }
        $urls[] = str_replace(
            '::test_',
            '',
            Str::snake(
                str_replace(
                    'ControllerTest',
                    '/',
                    end($t1)
                )
            )
        );
        return [$t1[2], '/' . implode('/', $urls)];
    }
}
