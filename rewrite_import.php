<?php
$serviceFile = __DIR__ . '/Modules/Educational/Application/Services/StudentImportExportService.php';
$content = file_get_contents($serviceFile);

// Remove the old import method completely
$start = strpos($content, 'public function import($filePath)');
$end = strpos($content, 'private function resolveId', $start);
$importMethodCode = substr($content, $start, $end - $start);

$newMethods = <<<PHP
public function prepareImport(\$filePath)
    {
        \$handle = fopen(\$filePath, 'r');
        \$bom = fread(\$handle, 3);
        if (\$bom !== chr(0xEF) . chr(0xBB) . chr(0xBF)) {
            rewind(\$handle);
        }
        \$header = fgetcsv(\$handle);
        if (!\$header) {
            fclose(\$handle);
            throw new \Exception("Invalid CSV file header.");
        }

        \$duplicates = [];
        \$errors = [];
        \$rowNum = 1;
        
        while ((\$data = fgetcsv(\$handle)) !== FALSE) {
            \$rowNum++;
            \$data = array_map('trim', \$data);
            if (count(\$data) < 3 || empty(implode('', \$data))) continue;
            
            \$data = array_slice(\$data, 0, count(\$this->columns));
            \$row = array_combine(\$this->columns, array_pad(\$data, count(\$this->columns), ''));
            
            if (empty(\$row['email']) || empty(\$row['username']) || empty(\$row['first_name'])) {
                \$errors[] = "السطر \$rowNum: بيانات ناقصة.";
                continue;
            }
            
            \$user = User::where('email', \$row['email'])->orWhere('username', \$row['username'])->first();
            if (\$user) {
                \$profile = TraineeProfile::where('user_id', \$user->id)->first();
                if (\$profile) {
                    // Active duplicate
                    \$duplicates[] = [
                        'row' => \$rowNum,
                        'email' => \$row['email'],
                        'username' => \$row['username'],
                        'current_name' => \$profile->arabic_name ?? \$user->first_name,
                        'new_name' => \$row['arabic_name'] ?? \$row['first_name'],
                    ];
                }
            }
        }
        fclose(\$handle);
        
        return [
            'duplicates' => \$duplicates,
            'errors' => \$errors
        ];
    }

    public function processImport(\$filePath, array \$updateEmails = [])
    {
        \$handle = fopen(\$filePath, 'r');
        \$bom = fread(\$handle, 3);
        if (\$bom !== chr(0xEF) . chr(0xBB) . chr(0xBF)) rewind(\$handle);
        \$header = fgetcsv(\$handle);
        
        \$count = 0;
        \$errors = [];
        \$rowNum = 1;
        \$roleName = 'Trainee';

        DB::beginTransaction();
        try {
            while ((\$data = fgetcsv(\$handle)) !== FALSE) {
                \$rowNum++;
                \$data = array_map('trim', \$data);
                if (count(\$data) < 3 || empty(implode('', \$data))) continue;
                
                \$data = array_slice(\$data, 0, count(\$this->columns));
                \$row = array_combine(\$this->columns, array_pad(\$data, count(\$this->columns), ''));
                
                if (empty(\$row['email']) || empty(\$row['username']) || empty(\$row['first_name'])) continue;
                
                \$user = User::where('email', \$row['email'])->orWhere('username', \$row['username'])->first();
                \$profile = \$user ? TraineeProfile::where('user_id', \$user->id)->first() : null;
                
                if (\$user && \$profile && !in_array(\$row['email'], \$updateEmails)) {
                    continue; // Skip duplicate that wasn't approved
                }
                
                \$governorateId = \$this->resolveId(Governorate::class, \$row['governorate_id']);
                \$programId = \$this->resolveId(Program::class, \$row['program_id']);
                \$groupId = \$this->resolveId(Group::class, \$row['group_id']);
                \$jobProfileId = \$this->resolveId(JobProfile::class, \$row['job_profile_id']);
                
                try {
                    if (!\$user) {
                        \$user = User::create([
                            'first_name' => \$row['first_name'],
                            'last_name' => \$row['last_name'],
                            'email' => \$row['email'],
                            'phone' => \$row['phone'],
                            'username' => \$row['username'],
                            'password' => Hash::make(\$row['password'] ?: 'student123'),
                            'status' => 'active',
                            'joined_at' => now(),
                        ]);
                        if (method_exists(\$user, 'assignRole')) {
                            try { \$user->assignRole(\$roleName); } catch (\Exception \$e) {}
                        }
                    } else {
                        // User exists
                        \$user->update([
                            'first_name' => \$row['first_name'],
                            'last_name' => \$row['last_name'],
                            'phone' => \$row['phone'],
                        ]);
                    }

                    \$profileData = [
                        'user_id' => \$user->id,
                        'enrollment_status' => \$row['enrollment_status'] ?: 'active',
                        'arabic_name' => \$row['arabic_name'],
                        'english_name' => \$row['english_name'],
                        'national_id' => \$row['national_id'] ?: null,
                        'passport_number' => \$row['passport_number'] ?: null,
                        'date_of_birth' => !empty(\$row['date_of_birth']) ? date('Y-m-d', strtotime(\$row['date_of_birth'])) : null,
                        'gender' => strtolower(\$row['gender']) == 'female' ? 'female' : 'male',
                        'address' => \$row['address'],
                        'governorate_id' => \$governorateId,
                        'program_id' => \$programId,
                        'group_id' => \$groupId,
                        'job_profile_id' => \$jobProfileId,
                        'religion' => \$row['religion'] ?? null,
                        'sect' => \$row['sect'] ?? null,
                        'device_code' => \$row['device_code'] ?? null,
                        'military_number' => \$row['military_number'] ?? null,
                    ];

                    if (!\$profile) {
                        TraineeProfile::create(\$profileData);
                        \Modules\Core\Domain\Models\AuditLog::create([
                            'user_id' => auth()->id() ?? \$user->id,
                            'auditable_type' => TraineeProfile::class,
                            'auditable_id' => \$user->id,
                            'action' => 'restored',
                            'old_values' => null,
                            'new_values' => ['message' => 'تم استعادة حساب الطالب وتحديث بياناته بالاستيراد'],
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                        ]);
                    } else {
                        \$profile->update(\$profileData);
                    }
                    \$count++;
                } catch (\Exception \$e) {
                    \$errors[] = "السطر \$rowNum: " . \$this->sanitizeErrorMessage(\$e->getMessage());
                }
            }
            if (!empty(\$errors)) {
                DB::rollBack();
                \$count = 0;
            } else {
                DB::commit();
            }
        } catch (\Exception \$e) {
            DB::rollBack();
            Log::error("Student Import failed: " . \$e->getMessage());
            \$count = 0;
        }

        fclose(\$handle);
        return ['count' => \$count, 'errors' => \$errors];
    }
PHP;

$content = str_replace($importMethodCode, $newMethods . "\n    /**\n     ", $content);
file_put_contents($serviceFile, $content);
echo "StudentImportExportService updated.\n";

// Now update StudentController
$controllerFile = __DIR__ . '/Modules/Educational/Http/Controllers/Web/StudentController.php';
$controllerContent = file_get_contents($controllerFile);

$start = strpos($controllerContent, 'public function import(Request $request, StudentImportExportService $service)');
$end = strpos($controllerContent, 'public function settings()', $start);
$importMethodCode = substr($controllerContent, $start, $end - $start);

$newMethods = <<<PHP
public function import(Request \$request, StudentImportExportService \$service)
    {
        \$request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        try {
            \$file = \$request->file('file');
            \$path = \$file->storeAs('temp_imports', 'students_'.time().'.csv', 'local');
            \$fullPath = storage_path('app/' . \$path);

            \$prepare = \$service->prepareImport(\$fullPath);

            if (!empty(\$prepare['duplicates'])) {
                \$duplicates = \$prepare['duplicates'];
                return view('educational::students.import_preview', compact('duplicates', 'path'));
            }

            \$result = \$service->processImport(\$fullPath, []);
            @unlink(\$fullPath);

            \$msg = "تم استيراد {\$result['count']} متدرب بنجاح.";
            if (!empty(\$result['errors'])) {
                return redirect()->back()
                    ->with('warning', \$msg . " ولكن توجد بعض الأخطاء في بعض الأسطر.")
                    ->with('import_errors', \$result['errors']);
            }
            return redirect()->back()->with('success', \$msg);
        } catch (\Exception \$e) {
            return redirect()->back()->with('error', "فشل الاستيراد: " . \$e->getMessage());
        }
    }

    public function confirmImport(Request \$request, StudentImportExportService \$service)
    {
        \$request->validate([
            'file_path' => 'required',
            'update_emails' => 'nullable|array'
        ]);

        try {
            \$fullPath = storage_path('app/' . \$request->file_path);
            if (!file_exists(\$fullPath)) {
                return redirect()->route('educational.students.index')->with('error', 'انتهت صلاحية الجلسة أو الملف غير موجود.');
            }

            \$result = \$service->processImport(\$fullPath, \$request->update_emails ?? []);
            @unlink(\$fullPath);

            \$msg = "تم استيراد وتحديث {\$result['count']} متدرب بنجاح.";
            if (!empty(\$result['errors'])) {
                return redirect()->route('educational.students.index')
                    ->with('warning', \$msg . " ولكن توجد خيارات لم يتم تحديثها بسبب أخطاء.")
                    ->with('import_errors', \$result['errors']);
            }

            return redirect()->route('educational.students.index')->with('success', \$msg);
        } catch (\Exception \$e) {
            return redirect()->route('educational.students.index')->with('error', "فشل الاستيراد: " . \$e->getMessage());
        }
    }

    
PHP;

$controllerContent = str_replace($importMethodCode, $newMethods, $controllerContent);
file_put_contents($controllerFile, $controllerContent);
echo "StudentController updated.\n";
