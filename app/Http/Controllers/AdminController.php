<?php

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use Illuminate\Auth\Events\Registered;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Validation\Rules;

    use App\Models\Pemerintah;
    use App\Models\Masyarakat;
    use App\Models\user;

    class AdminController extends Controller
    {
        private const ERROR_UNAUTHORIZED = 'You are not authorized!';
        private const RULE_REQUIRED_STRING = 'required|string';
        public function storePemerintah(Request $request)
        {
            if (!$this->checkRole("admin")) {
                return response()->json(['error' => self::ERROR_UNAUTHORIZED], 401);
            }
            try {
                $request->validate([
                    'name' => self::RULE_REQUIRED_STRING,
                    'username' => 'required|string|max:255|unique:user',
                    'phone' => self::RULE_REQUIRED_STRING,
                    'password' => ['required', Rules\Password::defaults()],
                    'institusi_id' => 'nullable|exists:institusi,id',
                    'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif',
                    'status' => 'required|boolean',
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json(['errors' => $e->errors()], 422);
            }

            $fotoPath = $this->uploadImage($request->file('foto'), 'users');

            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'foto' => $fotoPath,
            ]);

            Pemerintah::create([
                'id' => $user->id,
                'status' => $request->status,
                'phone' => $request->phone,
                'institusi_id' => $request->institusi_id,
            ]);

            event(new Registered($user));

            return response()->noContent();
        }

        public function updatePemerintah(Request $request, $id)
        {
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string|max:255',
                'username' => "nullable|string|max:255|unique:user,username,$id",
                'phone' => 'nullable|string|max:255',
                'password' => ['nullable', Rules\Password::defaults()],
                'institusi_id' => 'nullable|exists:institusi,id',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif',
                'status' => 'nullable|boolean',
            ]);
        
            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
        
            $user = User::find($id);
            $pemerintah = Pemerintah::find($id);
        
            if (!$user || !$pemerintah) {
                return response()->json(['message' => 'User or Pemerintah not found'], 404);
            }
        
            // Handle file upload for foto (if provided)
            if ($request->hasFile('foto')) {
                if ($user->foto) {
                    $this->deleteImage($user->foto);
                }
                
                $user->foto = $this->uploadImage($request->file('foto'), 'users');
            }
        
            // Handle text fields for User (name, username, password)
            if ($request->has('name')) {
                $user->name = $request->input('name');
            }
            if ($request->has('username')) {
                $user->username = $request->input('username');
            }
            if ($request->has('password')) {
                $user->password = Hash::make($request->input('password'));
            }
        
            $user->save();
        
            if ($request->has('phone')) {
                $pemerintah->phone = $request->input('phone');
            }
            if ($request->has('institusi_id')) {
                $pemerintah->institusi_id = $request->input('institusi_id');
            }
            if ($request->has('status')) {
                $pemerintah->status = $request->input('status');
            }
        
            $pemerintah->save();
        
            return response()->json([
                'message' => 'User and Pemerintah updated successfully',
                'user' => $user,
                'pemerintah' => $pemerintah
            ]);
        }

        public function indexPemerintah()
        {
            if (!$this->checkRole("admin")) {
                return response()->json(['error' => self::ERROR_UNAUTHORIZED], 401);
            }

            $pemerintah = Pemerintah::paginate(10);

            foreach ($pemerintah as $pemerintahData) {
                $pemerintahData->username = $pemerintahData->user->username;
                $pemerintahData->name = $pemerintahData->user->name;
                $pemerintahData->institusiName = $pemerintahData->institusi->name;
            }


            return response()->json(
                $pemerintah,
            );
        }

        public function showPemerintah($id)
        {
            if (!$this->checkRole("admin")) {
                return response()->json(['error' => self::ERROR_UNAUTHORIZED], 401);
            }

            $pemerintah = Pemerintah::with('user')->find($id);

            if (!$pemerintah) {
                return response()->json(['message' => 'Pemerintah not found'], 404);
            }
            $pemerintah->user;
            $pemerintah->institusi;
            return response()->json(
                $pemerintah,
            );
        }

        public function searchPemerintah(Request $request)
    {
        $search = $request->input('search');

        $pemerintah = Pemerintah::with(['user', 'institusi'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%$search%")
                        ->orWhere('username', 'like', "%$search%");
                })->orWhere('phone', 'like', "%$search%")
                ->orWhereHas('institusi', function ($institusiQuery) use ($search) {
                    $institusiQuery->where('name', 'like', "%$search%");
                });
            })
            ->paginate(10);

        $pemerintah->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'status' => $item->status,
                'phone' => $item->phone,
                'institusi_id' => $item->institusi_id,
                'username' => $item->user->username,
                'name' => $item->user->name,
                'institusiName' => $item->institusi->name ?? null,
                'user' => $item->user,
                'institusi' => $item->institusi,
            ];
        });

        return response()->json($pemerintah);
    }

        public function destroyPemerintah($id)
        {
            if (!$this->checkRole("admin")) {
                return response()->json(['error' => self::ERROR_UNAUTHORIZED], 401);
            }
            $pemerintah = Pemerintah::find($id);

            if (!$pemerintah) {
                return response()->json(['message' => 'Pemerintah not found'], 404);
            }

            $user = $pemerintah->user;
            if ($user) {
                if ($user->foto) {
                    $this->deleteImage($user->foto);
                }
                $pemerintah->delete();
                $user->delete();
            }


            return response()->json(['message' => 'Pemerintah and associated user deleted successfully']);
        }
        
        public function searchMasyarakatByPhone(Request $request)
        {
            $search = $request->input('search');
        $masyarakat = Masyarakat::where('phone',$search)->first();
        if(!$masyarakat){
            return response()->json(['message' => 'Masyarakat not found'], 404);
        }
        $user = User::where('id',$masyarakat->id)->first();
        return response()->json($user);

        
        }

        public function ubahPassword(Request $request)
        {

            $validator = Validator::make($request->all(), [
                'new_password' => ['required', Rules\Password::defaults()],
                'username' => self::RULE_REQUIRED_STRING,
            ]);
        
            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = User::where('username',$request->input('username'))->first();
            
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);

            }
            
            $user->password = Hash::make($request->input('new_password'));
            $user->save();

            return response()->json(['message' => 'Password updated successfully','user' => $user]);
        }


    }
