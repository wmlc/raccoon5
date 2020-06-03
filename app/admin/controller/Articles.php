<?php


namespace app\admin\controller;


use app\model\Article;
use Overtrue\Pinyin\Pinyin;
use think\facade\App;
use think\facade\View;

class Articles extends BaseAdmin
{
    protected $articleService;
    protected function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->articleService = app('articleService');
    }

    public function index() {
        $data = $this->articleService->getPagedAdmin();
        View::assign([
            'articles' => $data['articles'],
            'count' => $data['count']
        ]);
        return view();
    }

    public function create() {
        if (request()->isPost()) {
            $title = trim(input('title'));
            $article = new Article();
            $article['title'] = $title;
            $article['unique_id'] = $this->convert($title);
            $article['desc'] = input('desc');
            $article['book_id'] = input('book_id');
            $content = input('content');
            $dir = App::getRootPath().'public/static/upload/article/';
            $savename = md5($title).'.txt';
            file_put_contents($dir.$savename, $content);
            $article['content_url'] = '/static/upload/article/'.$savename;
            $article->save();
            $this->success('添加成功','index',1);
        }
        return view();
    }

    public function search() {
        $title = input('title');
        $where = [
            ['title', 'like', '%' . $title . '%']
        ];
        $data = $this->articleService->getPagedAdmin($where);
        View::assign([
            'articles' => $data['articles'],
            'count' => $data['count']
        ]);
        return view('index');
    }

    public function delete()
    {
        $id = input('id');
        $result = Article::destroy($id);
        if ($result) {
            return json(['err' => '0','msg' => '删除成功']);
        } else {
            return json(['err' => '1','msg' => '删除失败']);
        }
    }

    public function deleteAll($ids){
        $ids = input('ids');
        $result = Article::destroy($ids);
        if ($result) {
            return json(['err' => '0','msg' => '删除成功']);
        } else {
            return json(['err' => '1','msg' => '删除失败']);
        }
    }

    protected function convert($str){
        $pinyin = new Pinyin();
        $name_format = config('seo.name_format');
        switch ($name_format) {
            case 'pure':
                $arr = $pinyin->convert($str);
                $str = implode($arr,'');
                halt($str);
                break;
            case 'abbr':
                $str = $pinyin->abbr($str);break;
            default:
                $str = $pinyin->convert($str);break;
        }
        return $str;
    }
}