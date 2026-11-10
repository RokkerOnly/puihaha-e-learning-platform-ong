<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task List</title>
    <style>
         * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            color: #1e293b;
            background: #eef2ff;
        }

        .page {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            padding: 60px 20px;
        }

        .header {
            margin-bottom: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0 0 10px;
            color: #1e1b4b;
            font-size: 42px;
        }

        .header p {
            margin: 0;
            color: #64748b;
            font-size: 16px;
        }

        .card {
            margin-bottom: 25px;
            padding: 30px;
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(30, 41, 59, 0.12);
        }

        .card h2 {
            margin-top: 0;
            margin-bottom: 22px;
            color: #312e81;
            text-align: center;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            color: #334155;
            font-weight: bold;
        }

        input,
        textarea {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            outline: none;
            font-size: 15px;
            background: #f8fafc;
        }

        input:focus,
        textarea:focus {
            border-color: #6366f1;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .button {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 9px;
            color: white;
            background: #4f46e5;
            cursor: pointer;
            font-size: 15px;
            font-weight: bold;
        }

        .button:hover {
            background: #4338ca;
        }

        .message {
            margin-bottom: 20px;
            padding: 13px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
        }

        .success {
            color: #166534;
            background: #dcfce7;
        }

        .error {
            color: #991b1b;
            background: #fee2e2;
        }

        .task {
            margin-bottom: 15px;
            padding: 20px;
            border-left: 5px solid #6366f1;
            border-radius: 10px;
            background: #f8fafc;
        }

        .task h3 {
            margin: 0 0 8px;
            color: #312e81;
        }

        .task p {
            margin: 6px 0;
            color: #64748b;
            line-height: 1.5;
        }

        .status {
            display: inline-block;
            margin-top: 8px;
            padding: 5px 10px;
            border-radius: 20px;
            color: #92400e;
            background: #fef3c7;
            font-size: 12px;
            font-weight: bold;
        }

        .empty {
            padding: 25px;
            border: 2px dashed #cbd5e1;
            border-radius: 10px;
            color: #64748b;
            text-align: center;
        }

        @media (max-width: 600px) {
            .page {
                padding: 35px 15px;
            }

            .header h1 {
                font-size: 34px;
            }

            .card {
                padding: 22px;
            }
        }
</style>
</head>

<body>
    <main class="page">

        <header class="header">
            <h1>My Tasks</h1>
            <p>Organize your work and stay productive.</p>
        </header>

        @if (session('success'))
            <div class="message success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="message error">
                Please fix the errors in the form.
            </div>
        @endif

        <section class="card">
            <h2>Add New Task</h2>

            <form action="{{ route('tasks.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="task_title">Task Title</label>

                    <input
                        type="text"
                        id="task_title"
                        name="task_title"
                        value="{{ old('task_title') }}"
                        placeholder="Enter your task title"
                        required
                    >

                    @error('task_title')
                        <p class="error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Description</label>

                    <textarea
                        id="description"
                        name="description"
                        placeholder="Describe your task"
                    >{{ old('description') }}</textarea>
                </div>

                <button class="button" type="submit">
                    Add Task
                </button>
            </form>
        </section>

        <section class="card">
            <h2>Saved Tasks</h2>

            @forelse ($tasks as $task)
                <article class="task">
                    <h3>{{ $task->task_title }}</h3>

                    <p>
                        {{ $task->description ?: 'No description provided.' }}
                    </p>

                    <span class="status">
                        Status:
                        {{ $task->completed ? 'Completed' : 'Not completed' }}
                    </span>
                </article>
            @empty
                <div class="empty">
                    No tasks have been added yet.
                </div>
            @endforelse
        </section>

    </main>
</body>

</html>