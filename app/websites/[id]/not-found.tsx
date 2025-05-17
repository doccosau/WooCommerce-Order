import { Button } from "@/components/ui/button"
import { ArrowLeft } from "lucide-react"
import Link from "next/link"

export default function WebsiteNotFound() {
  return (
    <div className="container mx-auto py-6 flex flex-col items-center justify-center min-h-[60vh]">
      <h1 className="text-4xl font-bold mb-4">Website không tồn tại</h1>
      <p className="text-muted-foreground mb-6">Website bạn đang tìm kiếm không tồn tại hoặc đã bị xóa.</p>
      <Button asChild>
        <Link href="/websites">
          <ArrowLeft className="mr-2 h-4 w-4" />
          Quay lại danh sách website
        </Link>
      </Button>
    </div>
  )
}
