export interface ApiResponse<T = any> {
    success: boolean;
    message: string;
    data: T;
}

export interface PaginatedResponse<T = any> extends ApiResponse<T> {
    pagination: {
        current_page: number;
        per_page: number;
        total: number;
        last_page: number;
        next_page_url: string | null;
        prev_page_url: string | null;
    };
}