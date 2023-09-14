<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\UserSection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\SectionRequest;

class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Section::all();
    }

    public function list_sections_to_SP()
    {
        $sections_of_sp = UserSection::where('user_sections.user_id',Auth::id())
        ->select("user_sections.section_id")
        ->join('sections','sections.id','=','user_sections.section_id')
        ->pluck('usersections.section_id')->toArray();

        $sections =  Section::where('is_verified', "y")
        ->whereNotIn('id', $sections_of_sp)
        ->get();
        return response($sections, Response::HTTP_OK);
    }

    public function list_SP_sections()
    {
//        $section=UserSection::with('section_name')->where('user_id',\Auth::user()->id)->get();
        $section=UserSection::where('user_sections.user_id',\Auth::user()->id)
            ->select("sections.section_title","sections.section_title_ar", "sections.is_verified","sections.icon_id" ,"user_sections.id","sections.id as original_section_id")
            ->join('sections','sections.id','=','user_sections.section_id')
            ->get();

        return $section;
    }

    public function add_section_details(Request $request,$user_section_id)
    {
        $user_section=UserSection::find($user_section_id);
        $user_section->update($request->only('from','to','waiting_time_in_mins','charge'));
        if($request['days_array']){
            $user_section->store_days($request['days_array']);
        }
        return $user_section;
    }

    public function suggest_field(SectionRequest $request)
    {
        $section= Section::create([

            'section_title' => $request->input('section_title')  ,
            'section_title_ar' => $request->input('section_title_ar')  ,
            'is_verified' =>"n",
            'description' => $request->input('section_description') ?? null,
            'icon_id' => $request->input('icon_id'),
        ]);

        $user_section=UserSection::create(['user_id'=>\Auth::user()->id,'section_id'=>$section->id ]);
        return response($section,Response::HTTP_CREATED);
    }

    public function assign_section_to_sp(Request $request,$section_id)
    {
        $user_section=UserSection::where(['user_id'=>\Auth::user()->id,'section_id'=>$section_id])->first();
        if (! $user_section)
        {
            $user_section = UserSection::create(['user_id' => \Auth::user()->id, 'section_id' => $section_id,'is_emergency'=> $request->input('is_emergency')]);
        }else if($request->input('is_emergency') != $user_section->is_emergency){
            $user_section->is_emergency=$request->input('is_emergency');
            $user_section->save();
        }
        return response($user_section,Response::HTTP_CREATED);
    }

    public function unassign_section_to_sp($section_id)
    {
        $main_section=Section::find($section_id);
        $user_section=UserSection::where(['user_id'=>\Auth::user()->id,'section_id'=>$section_id])->first();
        if ($user_section)
        {
            UserSection::destroy($user_section->id);
        }

        if($main_section&&$main_section->is_verified == "n") {
            $main_section->delete();
        }

        return response(["result"=> true]);
    }




    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param SectionRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(SectionRequest $request)
    {
        //
        $section= Section::create([

            'section_title' => $request->input('section_title')  ,
            'section_title_ar' => $request->input('section_title_ar')  ,
            'is_verified' =>"y",
            'description' => $request->input('section_description') ?? null,
            'icon_id' => $request->input('icon_id'),
        ]);
        return response($section,Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    public function verify($section_id){
        $section=Section::find($section_id);
        $section->is_verified="y";
        $section->save();
        return $section;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param SectionRequest $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(SectionRequest $request, $id)
    {
        $section=Section::find($id);
        $section->update($request->only('section_title','section_title_ar', 'section_description','icon_id'));
        return response($section,Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Section::destroy($id);
        return response(null,204);

    }

    //for website

    public function getFiveSections(){
        $sections = UserSection::where(['user_sections.user_id' => Auth::id(),'sections.is_verified' => "y"])
        ->select('sections.id','sections.section_title', 'sections.section_title_ar', 'sections.section_description','sections.icon_id')
        ->join('sections','sections.id', '=', 'user_sections.section_id')
        ->take(5)->get();
        return response($sections, Response::HTTP_OK);
    }

    public function getSixSystemSections(){
        $sections = Section::where(['is_verified' => "y"])
        ->select('id','section_title', 'section_title_ar', 'section_description','icon_id')
        ->take(6)->get();
        return response($sections, Response::HTTP_OK);
    }

    //end for website

/** for Dashboard */
     /** section Details */
     public function sectionDetails($id){
        $section = Section::whereId($id)->firstOrFail();

        return response($section, Response::HTTP_OK);

    }
    /** end of section Details */
/** */
}
