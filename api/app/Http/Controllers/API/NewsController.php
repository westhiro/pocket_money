<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Event;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    // ニュース一覧取得
    public function index(Request $request)
    {
        $limit = $request->get('limit', 10);
        $type = $request->get('type'); // general, event, market
        
        $query = News::with(['event.industry'])
            ->where('is_published', true)
            ->latest('published_at');
        
        if ($type) {
            $query->where('news_type', $type);
        }
        
        $news = $query->limit($limit)->get();
        
        return response()->json([
            'success' => true,
            'data' => $news->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'content' => $item->content,
                    'news_type' => $item->news_type,
                    'published_at' => $item->published_at->format('Y-m-d H:i:s'),
                    'event' => $item->event ? [
                        'id' => $item->event->id,
                        'event_type' => $item->event->event_type,
                        'industry' => $item->event->industry ? $item->event->industry->name : null,
                        'impact_percentage' => $item->event->impact_percentage,
                    ] : null,
                    'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                ];
            })
        ]);
    }
    
    // 個別ニュース取得
    public function show($id)
    {
        $news = News::with(['event.industry'])->find($id);
        
        if (!$news) {
            return response()->json([
                'success' => false,
                'message' => 'ニュースが見つかりません'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $news->id,
                'title' => $news->title,
                'content' => $news->content,
                'news_type' => $news->news_type,
                'published_at' => $news->published_at->format('Y-m-d H:i:s'),
                'event' => $news->event ? [
                    'id' => $news->event->id,
                    'title' => $news->event->title,
                    'description' => $news->event->description,
                    'event_type' => $news->event->event_type,
                    'industry' => $news->event->industry ? $news->event->industry->name : null,
                    'impact_percentage' => $news->event->impact_percentage,
                    'occurred_at' => $news->event->occurred_at->format('Y-m-d H:i:s'),
                ] : null,
                'created_at' => $news->created_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }
    
    // 最新の重要ニュース取得（ホームページ用）
    public function latest()
    {
        $news = News::with(['event.industry'])
            ->where('is_published', true)
            ->latest('published_at')
            ->limit(5)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $news->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'content' => mb_substr(strip_tags($item->content), 0, 100) . '...',
                    'news_type' => $item->news_type,
                    'published_at' => $item->published_at->format('Y-m-d H:i'),
                    'event_impact' => $item->event ? $item->event->impact_percentage : null,
                    'industry_affected' => $item->event && $item->event->industry ? $item->event->industry->name : null,
                ];
            })
        ]);
    }
    
    // イベント関連ニュース取得
    public function eventNews()
    {
        $news = News::with(['event.industry'])
            ->where('news_type', 'event')
            ->where('is_published', true)
            ->latest('published_at')
            ->limit(20)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $news->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'content' => $item->content,
                    'published_at' => $item->published_at->format('Y-m-d H:i:s'),
                    'event' => [
                        'event_type' => $item->event->event_type,
                        'industry' => $item->event->industry->name,
                        'impact_percentage' => $item->event->impact_percentage,
                        'occurred_at' => $item->event->occurred_at->format('Y-m-d H:i:s'),
                    ]
                ];
            })
        ]);
    }
}
