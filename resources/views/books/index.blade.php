<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2rem; font-weight: 700; color: #1a202c; margin-bottom: 0.5rem;">Books</h1>
        <p style="color: #718096; margin-top: 0.5rem;">Organize your transactions into separate books for better tracking.</p>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            @if($books->count() > 0)
                <p style="font-size: 0.875rem; color: #a0aec0;">
                    {{ $books->count() }} {{ Str::plural('book', $books->count()) }}
                    @if(in_array($role, ['primary_admin', 'admin']))
                        - {{ $books->where('user_has_access', true)->count() }} accessible to you
                    @endif
                </p>
            @endif
        </div>
        @if(in_array($role, ['primary_admin', 'admin']))
        <a href="{{ route('books.create') }}" style="display: inline-flex; align-items: center; background-color: #3b82f6; color: white; padding: 0.5rem 1rem; border-radius: 0.375rem; font-weight: 600; text-decoration: none;">
            <svg style="width: 1rem; height: 1rem; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Create Book
        </a>
        @endif
    </div>

    @if($books->isEmpty())
        <div style="text-align: center; padding: 3rem; background: #f7fafc; border-radius: 0.5rem;">
            <svg style="width: 4rem; height: 4rem; color: #cbd5e0; margin: 0 auto 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            <h3 style="font-size: 1.125rem; font-weight: 600; color: #1a202c; margin-bottom: 0.5rem;">
                @if(in_array($role, ['primary_admin', 'admin']))
                    No books yet
                @else
                    No books assigned to you
                @endif
            </h3>
            <p style="font-size: 0.875rem; color: #a0aec0; margin-bottom: 1.5rem;">
                @if(in_array($role, ['primary_admin', 'admin']))
                    Get started by creating your first book to organize your transactions.
                @else
                    Contact your administrator to get access to books.
                @endif
            </p>
            @if(in_array($role, ['primary_admin', 'admin']))
            <a href="{{ route('books.create') }}" style="display: inline-flex; align-items: center; background-color: #3b82f6; color: white; padding: 0.5rem 1rem; border-radius: 0.375rem; font-weight: 600; text-decoration: none;">
                <svg style="width: 1rem; height: 1rem; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create your first book
            </a>
            @endif
        </div>
    @else
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
            @foreach($books as $book)
                <div style="background: white; border-radius: 0.5rem; box-shadow: 0 1px 3px rgb(0 0 0 / 0.1); transition: box-shadow 0.2s; {{ !$book->user_has_access ? 'opacity: 0.7; border: 2px dashed #e2e8f0;' : '' }}">
                    <div style="padding: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                            <div style="display: flex; align-items: center;">
                                <div style="width: 2.5rem; height: 2.5rem; background: #3b82f6; border-radius: 0.375rem; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                                    <svg style="width: 1.5rem; height: 1.5rem; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 style="font-size: 1.125rem; font-weight: 600; color: #1a202c; margin-bottom: 0.25rem;">{{ $book->name }}</h3>
                                    <span style="font-size: 0.75rem; color: #a0aec0;">#{{ $book->id }}</span>
                                </div>
                            </div>
                            @if(in_array($role, ['primary_admin', 'admin']))
                                @if($book->user_has_access)
                                    <span style="background-color: #48bb78; color: white; font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 0.375rem;">You have access</span>
                                @else
                                    <span style="background-color: #a0aec0; color: white; font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 0.375rem;">No access</span>
                                @endif
                            @endif
                        </div>

                        @if($book->description)
                            <p style="color: #718096; font-size: 0.875rem; margin-bottom: 1rem; line-height: 1.5;">{{ Str::limit($book->description, 120) }}</p>
                        @else
                            <p style="color: #cbd5e0; font-size: 0.875rem; margin-bottom: 1rem; font-style: italic;">No description provided</p>
                        @endif

                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <div style="display: flex; align-items: center; font-size: 0.875rem; color: #a0aec0;">
                                <svg style="width: 1rem; height: 1rem; margin-right: 0.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                {{ $book->transactions()->count() }} {{ Str::plural('transaction', $book->transactions()->count()) }}
                            </div>
                            @if($book->user_has_access)
                                                                <a href="{{ route('books.show', $book) }}" style="font-size: 0.875rem; color: white; background-color: #3b82f6; padding: 0.25rem 0.75rem; border-radius: 0.375rem; text-decoration: none;">
                                    View →
                                </a>
                            @else
                                <span style="font-size: 0.875rem; color: #cbd5e0;">Access required</span>
                            @endif
                        </div>
                    </div>

                    <div style="background: #f7fafc; padding: 0.75rem 1.5rem; border-top: 1px solid #e2e8f0;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.75rem; color: #a0aec0;">Last updated {{ $book->updated_at->diffForHumans() }}</span>
                            @if(in_array($role, ['primary_admin', 'admin']))
                            <div style="display: flex; gap: 0.5rem;">
                                @if(!$book->user_has_access)
                                    <a href="{{ route('books.users', $book) }}" style="background-color: #edf2f7; color: #4a5568; padding: 0.25rem 0.5rem; border-radius: 0.375rem; display: inline-flex; align-items: center; text-decoration: none;" title="Manage access">
                                        <svg style="width: 0.875rem; height: 0.875rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                        </svg>
                                    </a>
                                @endif
                                <a href="{{ route('books.edit', $book) }}" style="background-color: #edf2f7; color: #4a5568; padding: 0.25rem 0.5rem; border-radius: 0.375rem; display: inline-flex; align-items: center; text-decoration: none;" title="Edit book">
                                    <svg style="width: 0.875rem; height: 0.875rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
